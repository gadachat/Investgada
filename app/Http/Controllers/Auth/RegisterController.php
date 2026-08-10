<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use App\Models\BinaryTree;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        $referralCode = $request->query('ref');
        $sponsor = null;

        if ($referralCode) {
            $sponsor = User::where('referral_code', $referralCode)->where('status', 'active')->first();
        }

        return view('auth.register', compact('referralCode', 'sponsor'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'username'         => ['required', 'string', 'max:30', 'unique:users', 'alpha_dash'],
            'email'            => ['required', 'email', 'max:150', 'unique:users'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'country'          => ['nullable', 'string', 'max:80'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code'    => ['nullable', 'string', 'exists:users,referral_code'],
            'binary_position'  => ['nullable', 'in:left,right'],
            'terms'            => ['required', 'accepted'],
        ]);

        $sponsor = null;
        $parent = null;
        $binaryPosition = $request->binary_position;
        $usedRefCode = $request->input('referral_code') ?? $request->query('ref');

        if ($usedRefCode) {
            $sponsor = User::where('referral_code', $usedRefCode)->first();

            if ($sponsor) {
                // Find placement: if sponsor has no binary node, use sponsor as parent
                // Otherwise find next available position in sponsor's binary subtree
                $parent = $this->findPlacementPosition($sponsor, $binaryPosition);
            }
        }

        // Generate unique referral code
        $referralCode = $this->generateReferralCode($request->username);

        $user = User::create([
            'name'             => $request->name,
            'username'         => $request->username,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'country'          => $request->country,
            'password'         => $request->password,
            'referral_code'    => $referralCode,
            'referred_by_code' => $sponsor?->referral_code ?? $usedRefCode,
            'sponsor_id'       => $sponsor?->id,
            'parent_id'        => $parent?->id,
            'binary_position'  => $parent ? ($binaryPosition ?? 'left') : null,
            'role'             => 'user',
            'status'           => 'active',
            'kyc_status'       => 'not_submitted',
        ]);

        // Create wallets
        $this->createWallets($user);

        // Create referral record
        if ($sponsor) {
            Referral::create([
                'referrer_id'   => $sponsor->id,
                'referred_id'   => $user->id,
                'referral_code' => $sponsor->referral_code,
                'status'        => 'active',
                'activated_at'  => now(),
            ]);
        }

        // Create binary tree node
        if ($parent) {
            $this->createBinaryNode($user, $parent, $binaryPosition ?? 'left');
        } else {
            // Root user (no sponsor) — standalone node
            BinaryTree::create([
                'user_id'   => $user->id,
                'parent_id' => null,
                'position'  => 'left',
                'level'     => 0,
            ]);
        }

        event(new Registered($user));

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome to ' . config('app.name', 'the platform') . '! Your account has been created.');
    }

    /**
     * Find the next available placement position in a sponsor's binary subtree.
     * Uses BFS to find the first node with an empty left or right child.
     */
    private function findPlacementPosition(User $sponsor, ?string &$position): ?User
    {
        $sponsorNode = BinaryTree::where('user_id', $sponsor->id)->first();

        if (!$sponsorNode) {
            // Sponsor has no node yet — place directly under sponsor
            $position = $position ?? 'left';
            return $sponsor;
        }

        // BFS through the subtree to find first available slot
        $queue = [$sponsorNode];
        while (!empty($queue)) {
            $node = array_shift($queue);

            // Prefer the requested position if available
            if ($position === 'left' && is_null($node->left_child_id)) {
                return User::find($node->user_id);
            }
            if ($position === 'right' && is_null($node->right_child_id)) {
                return User::find($node->user_id);
            }

            // If no position specified, take first available
            if (!$position) {
                if (is_null($node->left_child_id)) {
                    $position = 'left';
                    return User::find($node->user_id);
                }
                if (is_null($node->right_child_id)) {
                    $position = 'right';
                    return User::find($node->user_id);
                }
            }

            // Add children to queue
            if ($node->left_child_id) {
                $childNode = BinaryTree::where('user_id', $node->left_child_id)->first();
                if ($childNode) $queue[] = $childNode;
            }
            if ($node->right_child_id) {
                $childNode = BinaryTree::where('user_id', $node->right_child_id)->first();
                if ($childNode) $queue[] = $childNode;
            }
        }

        // Fallback: place under sponsor
        $position = $position ?? 'left';
        return $sponsor;
    }

    /**
     * Create a binary tree node and update parent's child reference.
     */
    private function createBinaryNode(User $user, User $parent, string $position): void
    {
        $parentNode = BinaryTree::where('user_id', $parent->id)->first();

        $level = $parentNode ? $parentNode->level + 1 : 1;

        BinaryTree::create([
            'user_id'   => $user->id,
            'parent_id' => $parent->id,
            'position'  => $position,
            'level'     => $level,
        ]);

        // Update parent's child pointer
        if ($parentNode) {
            if ($position === 'left') {
                $parentNode->update(['left_child_id' => $user->id]);
                $parentNode->increment('left_count');
            } else {
                $parentNode->update(['right_child_id' => $user->id]);
                $parentNode->increment('right_count');
            }

            // Propagate count up the tree
            $this->propagateCountUp($parent->id, $position, 1);
        }
    }

    /**
     * Propagate team count up the binary tree.
     */
    private function propagateCountUp(int $userId, string $side, int $count): void
    {
        $node = BinaryTree::where('user_id', $userId)->first();
        if (!$node || !$node->parent_id) return;

        $parent = BinaryTree::where('user_id', $node->parent_id)->first();
        if (!$parent) return;

        if ($side === 'left') {
            $parent->increment('left_count', $count);
        } else {
            $parent->increment('right_count', $count);
        }

        $this->propagateCountUp($node->parent_id, $node->position, $count);
    }

    /**
     * Create default wallets for a new user.
     */
    private function createWallets(User $user): void
    {
        $walletTypes = ['deposit', 'interest', 'commission', 'bonus', 'withdrawal', 'trading'];
        foreach ($walletTypes as $type) {
            Wallet::create([
                'user_id'  => $user->id,
                'type'     => $type,
                'currency' => 'USD',
                'balance'  => 0,
            ]);
        }
    }

    /**
     * Generate a unique referral code from username.
     */
    private function generateReferralCode(string $username): string
    {
        $base = strtoupper(Str::substr(Str::slug($username), 0, 6));
        $code = $base . Str::upper(Str::random(4));

        while (User::where('referral_code', $code)->exists()) {
            $code = $base . Str::upper(Str::random(4));
        }

        return $code;
    }
}
