{{-- Tawk.to Live Chat Widget --}}
{{-- Included only when enabled and property ID is configured --}}
@if(\App\Models\Setting::get('tawk_enabled', false) && \App\Models\Setting::get('tawk_property_id', ''))
@php
    $showOnAdmin = \App\Models\Setting::get('tawk_show_on_admin', false);
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    $shouldShow = !$isAdmin || $showOnAdmin;
@endphp
@if($shouldShow)
<script type="text/javascript">
var Tawk_API = Tawk_API || {};
var Tawk_LoadStart = new Date();
(function(){
    var s1 = document.createElement('script'), s0 = document.getElementsByTagName('script')[0];
    s1.async = true;
    s1.src = 'https://embed.tawk.to/{{ \App\Models\Setting::get('tawk_property_id', '') }}/{{ \App\Models\Setting::get('tawk_widget_id', 'default') }}';
    s1.charset = 'UTF-8';
    s1.setAttribute('crossorigin', '*');
    s0.parentNode.insertBefore(s1, s0);
})();
</script>
@endif
@endif
