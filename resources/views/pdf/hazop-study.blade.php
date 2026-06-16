<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a2e; background: #fff; }
        .page { padding: 22px 26px; }

        /* Header */
        .header { border-bottom: 3px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
        .header-inner { display: flex; justify-content: space-between; align-items: flex-end; }
        .logo-area h1 { font-size: 18px; font-weight: bold; color: #1d4ed8; letter-spacing: 1px; }
        .logo-area p  { font-size: 8px; color: #6b7280; margin-top: 2px; }
        .doc-meta { text-align: right; font-size: 8px; color: #6b7280; line-height: 1.6; }
        .doc-meta .doc-title { font-size: 13px; font-weight: bold; color: #1a1a2e; }

        /* Section */
        .section { margin-bottom: 12px; }
        .section-title {
            background: #1d4ed8; color: #fff;
            font-size: 9px; font-weight: bold;
            padding: 4px 8px; margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .section-title.green { background: #166534; }
        .section-title.orange { background: #9a3412; }
        .section-title.gray  { background: #374151; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 14px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 14px; }
        .grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 4px 14px; }
        .field { margin-bottom: 5px; }
        .field label { font-size: 7.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; display: block; }
        .field span  { font-size: 9px; color: #1a1a2e; font-weight: 500; }
        .full { grid-column: 1 / -1; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7.5px; font-weight: bold; }
        .badge-low      { background: #dcfce7; color: #166534; }
        .badge-medium   { background: #fef9c3; color: #854d0e; }
        .badge-high     { background: #ffedd5; color: #9a3412; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-info     { background: #dbeafe; color: #1d4ed8; }
        .badge-gray     { background: #f3f4f6; color: #374151; }
        .badge-success  { background: #dcfce7; color: #166534; }
        .badge-warning  { background: #fef9c3; color: #854d0e; }
        .badge-pending  { background: #fef9c3; color: #854d0e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* Score box */
        .score-box { text-align: center; padding: 8px; border-radius: 4px; }
        .score-box .num { font-size: 20px; font-weight: bold; }
        .score-box .lbl { font-size: 8px; }
        .score-low      { background: #dcfce7; color: #166534; }
        .score-medium   { background: #fef9c3; color: #854d0e; }
        .score-high     { background: #ffedd5; color: #9a3412; }
        .score-critical { background: #fee2e2; color: #991b1b; }

        /* Summary stats */
        .stats { display: flex; gap: 8px; margin-bottom: 12px; }
        .stat-card { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; text-align: center; }
        .stat-card .stat-num  { font-size: 16px; font-weight: bold; color: #1d4ed8; }
        .stat-card .stat-label { font-size: 7.5px; color: #6b7280; margin-top: 2px; }

        /* Node worksheet table — landscape */
        .worksheet-table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin-top: 6px; page-break-inside: auto; }
        .worksheet-table th {
            background: #1d4ed8; color: #fff;
            font-weight: bold; padding: 4px 4px;
            text-align: left; border: 1px solid #1e40af;
            vertical-align: top;
        }
        .worksheet-table td { padding: 4px 4px; border: 1px solid #e5e7eb; vertical-align: top; }
        .worksheet-table tr:nth-child(even) td { background: #f9fafb; }
        .worksheet-table tr { page-break-inside: avoid; }
        .cell-wrap { word-break: break-word; max-width: 120px; }

        /* Risk matrix legend */
        .legend { display: flex; gap: 8px; margin-bottom: 10px; font-size: 8px; }
        .legend-item { display: flex; align-items: center; gap: 4px; }
        .legend-dot { width: 10px; height: 10px; border-radius: 2px; }

        /* Signature */
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 20px; }
        .sig-box { border-top: 1px solid #374151; padding-top: 4px; }
        .sig-box label { font-size: 7.5px; color: #6b7280; }

        /* Procedure box */
        .proc-step { display: flex; gap: 10px; margin-bottom: 6px; padding: 6px; border-left: 3px solid #1d4ed8; background: #eff6ff; }
        .proc-num { font-size: 12px; font-weight: bold; color: #1d4ed8; min-width: 20px; }
        .proc-body .title { font-weight: bold; font-size: 9px; }
        .proc-body .desc  { font-size: 8px; color: #374151; margin-top: 2px; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #9ca3af; display: flex; justify-content: space-between; }

        /* Page break */
        .page-break { page-break-before: always; }

        /* Team list */
        .team-list { font-size: 8.5px; line-height: 2; }
        .team-list li { margin-left: 14px; }

        /* Guide word table */
        .gw-table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-top: 4px; }
        .gw-table th { background: #eff6ff; color: #1d4ed8; font-weight: bold; padding: 4px 6px; border: 1px solid #dbeafe; }
        .gw-table td { padding: 4px 6px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="page">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="header">
        <div class="header-inner">
            <div class="logo-area">
                <h1>NOVAREX</h1>
                <p>Health, Safety &amp; Environment | Process Safety — HAZOP Study</p>
            </div>
            <div class="doc-meta">
                <div class="doc-title">HAZOP Study Report</div>
                <div>Reference: {{ $study->study_ref }}</div>
                <div>Generated: {{ now()->format('d M Y, H:i') }}</div>
                <div>Standard: ISO 31010 | ISO 45001 | ISO 19011</div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         SECTION 1 — STUDY INFORMATION
    ================================================================ --}}
    <div class="section">
        <div class="section-title">1. Study Information</div>
        <div class="grid-2">
            <div class="field"><label>Study Title</label><span>{{ $study->title }}</span></div>
            <div class="field"><label>Study Reference</label><span>{{ $study->study_ref }}</span></div>
            <div class="field"><label>Project / Facility</label><span>{{ $study->project?->title ?? 'Company-wide' }}</span></div>
            <div class="field"><label>Department</label><span>{{ $study->department?->name ?? '—' }}</span></div>
            <div class="field"><label>Process / Facility Area</label><span>{{ $study->facility_area ?? '—' }}</span></div>
            <div class="field"><label>P&amp;ID / Drawing Reference</label><span>{{ $study->pid_reference ?? '—' }}</span></div>
            <div class="field"><label>Study Date</label><span>{{ $study->study_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="field"><label>HAZOP Facilitator</label><span>{{ $study->facilitator?->name ?? '—' }}</span></div>
            <div class="field"><label>Status</label>
                <span><span class="badge badge-{{ \App\Models\HazopStudy::STATUS_COLORS[$study->status] ?? 'gray' }}">
                    {{ \App\Models\HazopStudy::STATUS_LABELS[$study->status] ?? $study->status }}
                </span></span>
            </div>
            <div class="field"><label>Approved By</label><span>{{ $study->approvedBy?->name ?? 'Pending' }}</span></div>
        </div>
    </div>

    {{-- ================================================================
         SECTION 2 — SCOPE & OBJECTIVES
    ================================================================ --}}
    @if($study->study_scope || $study->study_objectives || $study->process_description)
    <div class="section">
        <div class="section-title">2. Scope, Objectives &amp; Process Description</div>
        @if($study->study_scope)
        <div class="field" style="margin-bottom:6px">
            <label>Study Scope</label>
            <span>{{ $study->study_scope }}</span>
        </div>
        @endif
        @if($study->study_objectives)
        <div class="field" style="margin-bottom:6px">
            <label>Study Objectives</label>
            <span>{{ $study->study_objectives }}</span>
        </div>
        @endif
        @if($study->process_description)
        <div class="field">
            <label>Process Description</label>
            <span>{{ $study->process_description }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ================================================================
         SECTION 3 — HAZOP TEAM
    ================================================================ --}}
    <div class="section">
        <div class="section-title">3. HAZOP Team Composition</div>
        <div class="grid-2">
            <div>
                <div class="field"><label>Facilitator / Chairperson</label>
                    <span>{{ $study->facilitator?->name ?? '—' }}</span></div>
                @if($study->team_members)
                <div class="field" style="margin-top:4px"><label>Team Members</label></div>
                <ul class="team-list">
                    @foreach((array) $study->team_members as $member)
                        <li>{{ $member }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            <div>
                <div class="field"><label>Reviewed By</label>
                    <span>{{ $study->reviewedBy?->name ?? '—' }}</span></div>
                <div class="field"><label>Review Date</label>
                    <span>{{ $study->review_date?->format('d M Y') ?? '—' }}</span></div>
                <div class="field"><label>Approved By</label>
                    <span>{{ $study->approvedBy?->name ?? '—' }}</span></div>
                <div class="field"><label>Approval Date</label>
                    <span>{{ $study->approval_date?->format('d M Y') ?? '—' }}</span></div>
                @if($study->approval_comments)
                <div class="field"><label>Approval Comments</label>
                    <span>{{ $study->approval_comments }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================
         SECTION 4 — RISK SUMMARY STATISTICS
    ================================================================ --}}
    <div class="section">
        <div class="section-title">4. Study Risk Summary</div>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-num">{{ $nodes->count() }}</div>
                <div class="stat-label">Total Nodes / Deviations</div>
            </div>
            <div class="stat-card" style="border-color:#fee2e2">
                <div class="stat-num" style="color:#991b1b">{{ $nodes->where('risk_classification','critical')->count() }}</div>
                <div class="stat-label">Critical Risk (81–125)</div>
            </div>
            <div class="stat-card" style="border-color:#ffedd5">
                <div class="stat-num" style="color:#9a3412">{{ $nodes->where('risk_classification','high')->count() }}</div>
                <div class="stat-label">High Risk (51–80)</div>
            </div>
            <div class="stat-card" style="border-color:#fef9c3">
                <div class="stat-num" style="color:#854d0e">{{ $nodes->where('risk_classification','medium')->count() }}</div>
                <div class="stat-label">Medium Risk (21–50)</div>
            </div>
            <div class="stat-card" style="border-color:#dcfce7">
                <div class="stat-num" style="color:#166534">{{ $nodes->where('risk_classification','low')->count() }}</div>
                <div class="stat-label">Low Risk (1–20)</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" style="color:#1d4ed8">{{ $nodes->where('status','closed')->count() }}</div>
                <div class="stat-label">Actions Closed</div>
            </div>
        </div>

        <div class="legend">
            <strong style="font-size:8px;">NOVAREX Risk Matrix:</strong>
            <div class="legend-item"><div class="legend-dot" style="background:#dcfce7;"></div> Low (1–20) — Supervisor approval</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fef9c3;"></div> Medium (21–50) — HSE Officer approval</div>
            <div class="legend-item"><div class="legend-dot" style="background:#ffedd5;"></div> High (51–80) — HSE Manager approval</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fee2e2;"></div> Critical (81–125) — HSE Manager + Top Management</div>
        </div>
    </div>

    {{-- ================================================================
         PAGE BREAK — WORKSHEET TABLE (landscape intent via wide layout)
    ================================================================ --}}
    <div class="page-break"></div>

    {{-- ================================================================
         SECTION 5 — HAZOP WORKSHEET
    ================================================================ --}}
    <div class="section">
        <div class="section-title">5. HAZOP Analysis Worksheet</div>

        @if($nodes->isEmpty())
            <p style="font-size:9px; color:#6b7280; padding:10px;">No nodes have been recorded for this study yet.</p>
        @else
        <table class="worksheet-table">
            <thead>
                <tr>
                    <th style="width:28px;">No.</th>
                    <th style="width:70px;">Node / Area</th>
                    <th style="width:55px;">Guide Word</th>
                    <th style="width:80px;">Deviation</th>
                    <th style="width:80px;">Cause</th>
                    <th style="width:80px;">Consequence</th>
                    <th style="width:70px;">Existing Safeguards</th>
                    <th style="width:52px;">Initial Risk<br>(L×S×E)</th>
                    <th style="width:38px;">RPN<br>(S×O×D)</th>
                    <th style="width:80px;">Recommended Actions</th>
                    <th style="width:32px;">CE%</th>
                    <th style="width:52px;">Residual Risk</th>
                    <th style="width:32px;">RRF</th>
                    <th style="width:55px;">Risk Owner</th>
                    <th style="width:45px;">Due Date</th>
                    <th style="width:42px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nodes as $node)
                @php
                    $irClass = $node->risk_classification;
                    $rrClass = $node->residual_risk_classification;
                    $badgeMap = ['low'=>'low','medium'=>'medium','high'=>'high','critical'=>'critical'];
                @endphp
                <tr>
                    <td style="text-align:center; font-weight:bold;">{{ $node->node_number ?? $loop->iteration }}</td>
                    <td><div class="cell-wrap">
                        @if($node->node_name)<strong>{{ $node->node_name }}</strong><br>@endif
                        @if($node->parameter)<span style="color:#6b7280;">{{ $node->parameter }}</span>@endif
                    </div></td>
                    <td>
                        @if($node->guide_word)
                        <span class="badge badge-info">{{ $node->guide_word }}</span>
                        @else —
                        @endif
                    </td>
                    <td><div class="cell-wrap">{{ $node->deviation ?? '—' }}</div></td>
                    <td><div class="cell-wrap">{{ $node->cause ?? '—' }}</div></td>
                    <td><div class="cell-wrap">{{ $node->consequence ?? '—' }}</div></td>
                    <td><div class="cell-wrap">{{ $node->existing_safeguards ?? '—' }}</div></td>
                    <td>
                        <span class="badge badge-{{ $badgeMap[$irClass] ?? 'gray' }}">
                            {{ $node->initial_risk_score }}<br>{{ ucfirst($irClass) }}
                        </span>
                        <div style="font-size:7px; color:#6b7280; margin-top:2px;">
                            L{{ $node->likelihood }}×S{{ $node->severity }}×E{{ $node->exposure }}
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $badgeMap[\App\Services\HazopScoringService::riskLevel($node->rpn_score)] ?? 'gray' }}">
                            {{ $node->rpn_score }}
                        </span>
                    </td>
                    <td><div class="cell-wrap">{{ $node->recommended_actions ?? '—' }}</div></td>
                    <td style="text-align:center;">
                        <strong>{{ number_format((float)$node->control_effectiveness, 0) }}%</strong>
                    </td>
                    <td>
                        <span class="badge badge-{{ $badgeMap[$rrClass] ?? 'gray' }}">
                            {{ number_format((float)$node->residual_risk_score, 1) }}<br>{{ ucfirst($rrClass) }}
                        </span>
                    </td>
                    <td style="text-align:center; font-weight:bold;">×{{ number_format((float)$node->risk_reduction_factor, 1) }}</td>
                    <td><div class="cell-wrap">{{ $node->riskOwner?->name ?? '—' }}</div></td>
                    <td>
                        {{ $node->due_date?->format('d M Y') ?? '—' }}
                        @if($node->due_date && $node->due_date->isPast() && $node->status !== 'closed')
                        <br><span style="color:#991b1b; font-weight:bold;">OVERDUE</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ match($node->status) {
                            'closed' => 'success',
                            'open'   => 'danger',
                            default  => 'warning',
                        } }}">
                            {{ \App\Models\HazopNode::STATUS_LABELS[$node->status] ?? $node->status }}
                        </span>
                        <br>
                        <span class="badge badge-{{ $node->approval_status === 'approved' ? 'approved' : ($node->approval_status === 'rejected' ? 'rejected' : 'pending') }}" style="margin-top:2px;">
                            {{ ucfirst($node->approval_status) }}
                        </span>
                    </td>
                </tr>
                @if($node->closure_verification)
                <tr>
                    <td colspan="16" style="background:#f0fdf4; padding:3px 6px;">
                        <strong style="color:#166534; font-size:7.5px;">✓ Closure Verification:</strong>
                        <span style="font-size:7.5px; color:#374151;">{{ $node->closure_verification }}</span>
                        @if($node->closureVerifiedBy)
                        — <em style="font-size:7.5px;">Verified by {{ $node->closureVerifiedBy?->name }} on {{ $node->closure_date?->format('d M Y') }}</em>
                        @endif
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ================================================================
         SECTION 6 — HAZOP PROCEDURE SUMMARY (ISO ALIGNED)
    ================================================================ --}}
    <div class="page-break"></div>

    <div class="section">
        <div class="section-title gray">6. HAZOP Methodology Reference (ISO 31010 / ISO 45001)</div>

        <table class="gw-table" style="margin-bottom:10px;">
            <thead>
                <tr>
                    <th>Guide Word</th>
                    <th>Meaning</th>
                    <th>Example Deviation</th>
                    <th>Typical Causes</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><strong>NO / NOT</strong></td><td>Complete negation of design intent</td><td>No Flow</td><td>Pump failure, blockage, power loss</td></tr>
                <tr><td><strong>MORE OF</strong></td><td>Quantitative increase</td><td>High Pressure, Excess Flow</td><td>Control valve failure open, runaway reaction</td></tr>
                <tr><td><strong>LESS OF</strong></td><td>Quantitative decrease</td><td>Low Flow, Low Pressure</td><td>Partial blockage, pump wear, leak</td></tr>
                <tr><td><strong>REVERSE</strong></td><td>Logical opposite of intent</td><td>Reverse Flow</td><td>Check valve failure, pump reverse rotation</td></tr>
                <tr><td><strong>AS WELL AS</strong></td><td>Additional activity or component</td><td>Contaminated stream</td><td>Cross-connection, wrong chemical addition</td></tr>
                <tr><td><strong>PART OF</strong></td><td>Only part of intent achieved</td><td>Incomplete reaction</td><td>Poor mixing, wrong temperature</td></tr>
                <tr><td><strong>OTHER THAN</strong></td><td>Complete substitution</td><td>Wrong chemical, wrong phase</td><td>Labelling error, wrong valve opened</td></tr>
                <tr><td><strong>EARLY / LATE</strong></td><td>Wrong timing relative to schedule</td><td>Premature batch end</td><td>Timer failure, operator error</td></tr>
            </tbody>
        </table>

        <div style="margin-bottom:10px;">
            <strong style="font-size:9px;">10-Step HAZOP Procedure (ISO 31010 Aligned):</strong>
        </div>

        @php
            $steps = [
                ['Define Study Scope', 'Identify the plant section, process unit, or system boundary. Define inclusions and exclusions.'],
                ['Form HAZOP Team', 'Chairperson (independent) + Process Engineer + Operations + Maintenance + HSE Officer + Instrumentation Engineer.'],
                ['Break System into Nodes', 'Divide system into discrete, manageable sections: pumps, tanks, reactors, pipelines, control loops.'],
                ['Define Design Intent', 'Document the intended operating conditions for each node (e.g. "Steady flow of 50 m³/hr at 10 bar").'],
                ['Apply Guide Words to Parameters', 'Systematically apply guide words (NO, MORE, LESS, REVERSE, AS WELL AS, OTHER THAN, EARLY, LATE) to each parameter (Flow, Pressure, Temperature, Level, Composition, etc.).'],
                ['Identify Deviations & Causes', 'For each guide word + parameter combination, identify credible deviations and all possible causes (equipment failure, human error, control system failure, external events).'],
                ['Analyse Consequences', 'Assess safety impact (injury, fire, explosion), environmental impact (spill, emission) and operational impact (production loss, equipment damage).'],
                ['Identify Safeguards & Calculate Risk', 'List existing safeguards (alarms, interlocks, PSVs, ESD). Calculate Initial Risk = L × S × E, then RPN = S × O × D.'],
                ['Recommend Actions & Calculate Residual Risk', 'Define engineering, administrative and maintenance improvements. Apply Control Effectiveness (CE%) to compute Residual Risk = IR × (1 − CE%). Calculate RRF = IR ÷ RR.'],
                ['Action Tracking & Close-out', 'Assign risk owner, set due date, track status through: Open → Action Assigned → In Progress → Verification Pending → Closed. Update risk register.'],
            ];
        @endphp

        @foreach($steps as $i => $step)
        <div class="proc-step" style="margin-bottom:4px;">
            <div class="proc-num">{{ $i + 1 }}</div>
            <div class="proc-body">
                <div class="title">{{ $step[0] }}</div>
                <div class="desc">{{ $step[1] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ================================================================
         RISK MATRIX TABLE
    ================================================================ --}}
    <div class="section">
        <div class="section-title gray">NOVAREX Quantitative Risk Matrix (L × S × E)</div>
        <table class="gw-table">
            <thead>
                <tr>
                    <th>Score Range</th>
                    <th>Risk Classification</th>
                    <th>Approval Requirement</th>
                    <th>Required Action</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#fee2e2;">
                    <td><strong>81–125</strong></td>
                    <td><span class="badge badge-critical">Critical</span></td>
                    <td>HSE Manager + Top Management (MD / CEO)</td>
                    <td>Stop work immediately. Senior management authorization required before resuming.</td>
                </tr>
                <tr style="background:#ffedd5;">
                    <td><strong>51–80</strong></td>
                    <td><span class="badge badge-high">High</span></td>
                    <td>HSE Manager</td>
                    <td>Immediate corrective actions required. Management review necessary.</td>
                </tr>
                <tr style="background:#fef9c3;">
                    <td><strong>21–50</strong></td>
                    <td><span class="badge badge-medium">Medium</span></td>
                    <td>HSE Officer</td>
                    <td>Implement additional controls where reasonably practicable.</td>
                </tr>
                <tr style="background:#dcfce7;">
                    <td><strong>1–20</strong></td>
                    <td><span class="badge badge-low">Low</span></td>
                    <td>Supervisor</td>
                    <td>Maintain existing controls and monitor periodically.</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:8px; font-size:8px; color:#6b7280;">
            <strong>Formula:</strong> Initial Risk Score = L × S × E (max 125) &nbsp;|&nbsp;
            Residual Risk = IR × (1 − CE%) &nbsp;|&nbsp;
            RRF = IR ÷ RR &nbsp;|&nbsp;
            RPN = Severity × Occurrence × Detectability (max 125)
        </div>
    </div>

    {{-- ================================================================
         SIGNATURES
    ================================================================ --}}
    <div class="sig-row" style="margin-top:16px;">
        <div class="sig-box">
            <label>HAZOP Facilitator</label><br><br><br>
            <span style="font-size:9px;">{{ $study->facilitator?->name ?? '___________________' }}</span>
            <div style="font-size:7.5px; color:#6b7280;">Name &amp; Signature</div>
        </div>
        <div class="sig-box">
            <label>HSE Manager / Reviewed By</label><br><br><br>
            <span style="font-size:9px;">{{ $study->reviewedBy?->name ?? '___________________' }}</span>
            <div style="font-size:7.5px; color:#6b7280;">Name &amp; Signature</div>
        </div>
        <div class="sig-box">
            <label>Approved By (MD / Senior Management)</label><br><br><br>
            <span style="font-size:9px;">{{ $study->approvedBy?->name ?? '___________________' }}</span>
            <div style="font-size:7.5px; color:#6b7280;">Name &amp; Signature</div>
        </div>
    </div>

    <div class="footer">
        <span>NOVAREX — Confidential | HAZOP Study Report | {{ $study->study_ref }}</span>
        <span>ISO 31010 | ISO 45001 | ISO 19011</span>
        <span>Generated: {{ now()->format('d M Y, H:i') }}</span>
    </div>

</div>
</body>
</html>
