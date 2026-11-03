@php
    $settingsTabs = [
        ['label' => 'Pengaturan Sekolah', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*'],
        ['label' => 'Backup Database', 'route' => 'admin.backups.index', 'pattern' => 'admin.backups.*'],
        ['label' => 'Restore Database', 'route' => 'admin.restore.form', 'pattern' => 'admin.restore.*'],
    ];
@endphp

<nav class="settings-tabs" aria-label="Menu pengaturan">
    @foreach($settingsTabs as $tab)
        <a href="{{ route($tab['route']) }}" class="{{ request()->routeIs($tab['pattern']) ? 'active' : '' }}">{{ $tab['label'] }}</a>
    @endforeach
</nav>
