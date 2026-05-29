@php
    $links = match (auth()->user()->role->value) {
        'doctor' => [
            ['route' => 'doctor.index', 'label' => __('Dashboard'), 'active' => 'doctor.index'],
            ['route' => 'doctor.files', 'label' => __('All files'),  'active' => 'doctor.files'],
            ['route' => 'doctor.duplicates', 'label' => __('Duplicates'), 'active' => 'doctor.duplicates'],
        ],
        'lab_admin' => [
            ['route' => 'lab.index', 'label' => __('Unassigned'), 'active' => 'lab.index'],
            ['route' => 'lab.files', 'label' => __('All files'),   'active' => 'lab.files'],
            ['route' => 'lab.doctors', 'label' => __('Doctors'),   'active' => 'lab.doctors'],
            ['route' => 'lab.insurers', 'label' => __('Insurers'), 'active' => 'lab.insurers'],
            ['route' => 'lab.analytics', 'label' => __('Analytics'), 'active' => 'lab.analytics'],
            ['route' => 'lab.activity', 'label' => __('Activity'), 'active' => 'lab.activity'],
        ],
        'insurance' => [
            ['route' => 'insurance.index', 'label' => __('My files'), 'active' => 'insurance.index'],
        ],
        default => [],
    };
@endphp

<div class="hidden md:flex items-center gap-1 text-sm">
    @foreach ($links as $l)
        <a href="{{ route($l['route']) }}"
           class="px-3 py-1.5 rounded-lg font-semibold transition {{ request()->routeIs($l['active']) ? 'bg-white/25 text-white' : 'text-brand-100 hover:bg-white/15 hover:text-white' }}">
            {{ $l['label'] }}
        </a>
    @endforeach
</div>
