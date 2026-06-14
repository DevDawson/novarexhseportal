{{--
    Shared letterhead for all DomPDF-rendered documents.
    Include at the top of each PDF blade template:

        @include('filament.pdf.partials.letterhead')

    Expects the parent template to define its own <style> block for
    fonts/colors; this partial adds its own minimal styles for the
    letterhead block only (scoped class names to avoid clashes).
--}}
@php
    $logoPath = \App\Models\Setting::logoAbsolutePath();
    $companyName = \App\Models\Setting::companyName();
    $companyTagline = \App\Models\Setting::companyTagline();
@endphp

<style>
    .letterhead { display: table; width: 100%; margin-bottom: 10px; border-bottom: 2px solid #3B82F6; padding-bottom: 8px; }
    .letterhead-logo { display: table-cell; width: 60px; vertical-align: middle; }
    .letterhead-logo img { max-width: 56px; max-height: 56px; }
    .letterhead-text { display: table-cell; vertical-align: middle; padding-left: 10px; }
    .letterhead-text .company-name { font-size: 15px; font-weight: bold; color: #1C2127; }
    .letterhead-text .company-tagline { font-size: 8.5px; color: #6B7280; margin-top: 1px; }
</style>

<div class="letterhead">
    @if ($logoPath)
        <div class="letterhead-logo">
            <img src="{{ $logoPath }}" alt="Logo">
        </div>
    @endif
    <div class="letterhead-text">
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-tagline">{{ $companyTagline }}</div>
    </div>
</div>
