<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Risk Matrix (Heatmap) — Likelihood × Severity</x-slot>
        <x-slot name="description">
            Initial risk (bold count) · Residual risk after controls (lighter count) · Score = Likelihood × Severity
        </x-slot>

        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:520px">

                {{-- Column headers: Severity 1–5 --}}
                <thead>
                    <tr>
                        <th style="width:140px;padding:8px;text-align:center;font-size:12px;color:#6B7280;border:1px solid #E5E7EB">
                            Likelihood ↓ / Severity →
                        </th>
                        @for($s = 1; $s <= 5; $s++)
                            <th style="padding:8px;text-align:center;font-size:12px;font-weight:600;color:#374151;border:1px solid #E5E7EB;background:#F9FAFB">
                                {{ $s }}<br><span style="font-weight:400;font-size:10px">{{ ['','Insignificant','Minor','Moderate','Major','Catastrophic'][$s] }}</span>
                            </th>
                        @endfor
                    </tr>
                </thead>

                <tbody>
                    @php $matrix = $this->getMatrix(); @endphp
                    @for($l = 5; $l >= 1; $l--)
                        <tr>
                            {{-- Row header: Likelihood --}}
                            <td style="padding:8px;text-align:center;font-size:12px;font-weight:600;color:#374151;border:1px solid #E5E7EB;background:#F9FAFB">
                                {{ $l }}<br><span style="font-weight:400;font-size:10px">{{ ['','Rare','Unlikely','Possible','Likely','Almost Certain'][$l] }}</span>
                            </td>

                            @for($s = 1; $s <= 5; $s++)
                                @php
                                    $bg     = \App\Filament\Widgets\RiskMatrixWidget::cellColor($l, $s);
                                    $lbl    = \App\Filament\Widgets\RiskMatrixWidget::cellLabel($l, $s);
                                    $score  = $l * $s;
                                    $cell   = $matrix[$l][$s] ?? ['initial' => 0, 'residual' => 0];
                                    $ini    = $cell['initial'];
                                    $res    = $cell['residual'];
                                @endphp
                                <td style="
                                    padding:10px 6px;
                                    text-align:center;
                                    border:1px solid rgba(0,0,0,0.08);
                                    background-color:{{ $bg }};
                                    color:#fff;
                                    vertical-align:middle;
                                    min-width:80px;
                                ">
                                    <div style="font-size:10px;font-weight:700;letter-spacing:0.05em;opacity:0.9">{{ $lbl }}</div>
                                    <div style="font-size:14px;font-weight:800;line-height:1.4">{{ $score }}</div>
                                    @if($ini > 0 || $res > 0)
                                        <div style="margin-top:4px;font-size:11px">
                                            <span style="font-weight:700" title="Initial risk count">{{ $ini }}</span>
                                            @if($res > 0)
                                                <span style="opacity:0.75;font-weight:400"> / {{ $res }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:16px;margin-top:14px;flex-wrap:wrap;align-items:center">
            @foreach([['#22C55E','Low (0–4)'],['#EAB308','Medium (5–9)'],['#F97316','High (10–15)'],['#EF4444','Critical (16–25)']] as [$col,$lbl])
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#374151">
                    <div style="width:14px;height:14px;border-radius:3px;background:{{ $col }}"></div>
                    {{ $lbl }}
                </div>
            @endforeach
            <span style="font-size:12px;color:#6B7280;margin-left:8px">Bold = initial · lighter = residual</span>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
