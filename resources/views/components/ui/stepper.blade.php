@props(['active'])

@php
    $steps = [
        'alternatif' => [
            'label' => 'Alternatif',
            'icon' => 'fa-boxes-stacked',
            'route' => 'supervisor.ahp.alternatif',
            'step' => 1
        ],
        'kriteria' => [
            'label' => 'Kriteria',
            'icon' => 'fa-list-ol',
            'route' => 'supervisor.ahp.kriteria',
            'step' => 2
        ],
        'subkriteria' => [
            'label' => 'Subkriteria',
            'icon' => 'fa-layer-group',
            'route' => 'supervisor.ahp.subkriteria',
            'step' => 3
        ],
        'supplier' => [
            'label' => 'Supplier',
            'icon' => 'fa-truck-field',
            'route' => 'supervisor.ahp.supplier',
            'step' => 4
        ],
        'hasil' => [
            'label' => 'Hasil & Ranking',
            'icon' => 'fa-square-poll-vertical',
            'route' => 'supervisor.ahp.hasil',
            'step' => 5
        ]
    ];

    $activeStep = $steps[$active]['step'] ?? 1;
@endphp

<div class="w-full py-6">
    <div class="flex items-center justify-between">
        @foreach($steps as $key => $step)
            @php
                $isCompleted = $step['step'] < $activeStep;
                $isActive = $step['step'] == $activeStep;
                $isUpcoming = $step['step'] > $activeStep;
            @endphp
            
            <div class="flex items-center flex-1 last:flex-initial">
                <a href="{{ Route::has($step['route']) ? route($step['route']) : '#' }}" 
                   class="flex flex-col items-center focus:outline-none group">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300
                        {{ $isActive ? 'border-teal bg-teal text-white shadow-lg shadow-teal/30 scale-110' : '' }}
                        {{ $isCompleted ? 'border-teal-dark bg-teal-dark text-white' : '' }}
                        {{ $isUpcoming ? 'border-slate-200 bg-white text-slate-400 group-hover:border-slate-300' : '' }}">
                        @if($isCompleted)
                            <i class="fas fa-check text-xs"></i>
                        @else
                            <i class="fas {{ $step['icon'] }} text-xs"></i>
                        @endif
                    </div>
                    
                    <span class="mt-2 text-xs font-semibold text-center transition-colors duration-200
                        {{ $isActive ? 'text-teal font-bold' : '' }}
                        {{ $isCompleted ? 'text-slate-600' : '' }}
                        {{ $isUpcoming ? 'text-slate-400 group-hover:text-slate-500' : '' }}">
                        {{ $step['label'] }}
                    </span>
                </a>

                @if($step['step'] < 5)
                    <div class="flex-1 h-0.5 mx-4 rounded-full transition-colors duration-500
                        {{ $step['step'] < $activeStep ? 'bg-teal-dark' : 'bg-slate-200' }}">
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
