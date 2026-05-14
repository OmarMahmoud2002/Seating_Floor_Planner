@php
    $steps = [
        1 => ['title' => 'بيانات الحدث', 'description' => 'اسم الحدث والموقع والتاريخ'],
        2 => ['title' => 'إعداد المخطط', 'description' => 'المقاس والورق والخلفية'],
        3 => ['title' => 'محرر المخطط', 'description' => 'ترتيب القاعة والتجليس'],
    ];
@endphp

<nav class="setup-stepper" aria-label="مراحل إنشاء الحدث">
    @foreach ($steps as $number => $step)
        @php
            $stateClass = $number < $currentStep ? 'is-complete' : ($number === $currentStep ? 'is-active' : 'is-upcoming');
        @endphp

        <div class="setup-step {{ $stateClass }}">
            <span class="setup-step-marker">{{ $number < $currentStep ? '✓' : $number }}</span>
            <span class="setup-step-text">
                <strong>{{ $step['title'] }}</strong>
                <small>{{ $step['description'] }}</small>
            </span>
        </div>
    @endforeach
</nav>
