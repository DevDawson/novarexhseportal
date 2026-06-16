<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }
        .page { padding: 30px 36px; }

        /* Header */
        .header { border-bottom: 3px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 18px; }
        .header-inner { display: flex; justify-content: space-between; align-items: flex-end; }
        .logo-area h1 { font-size: 20px; font-weight: bold; color: #1d4ed8; letter-spacing: 1px; }
        .logo-area p  { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .doc-meta { text-align: right; font-size: 9px; color: #6b7280; line-height: 1.7; }
        .doc-meta .doc-title { font-size: 14px; font-weight: bold; color: #1a1a2e; }

        /* Document cover info */
        .cover-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 14px 18px; margin-bottom: 18px; }
        .cover-box h2 { font-size: 15px; font-weight: bold; color: #1d4ed8; margin-bottom: 8px; }
        .cover-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; font-size: 9px; }
        .cover-field label { color: #6b7280; text-transform: uppercase; font-size: 8px; display: block; }
        .cover-field span { font-weight: 600; color: #1a1a2e; }

        /* Section */
        .section { margin-bottom: 16px; }
        .section-title {
            background: #1d4ed8; color: #fff;
            font-size: 10px; font-weight: bold;
            padding: 5px 10px; margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .section-title.green  { background: #166534; }
        .section-title.gray   { background: #374151; }
        .section-title.orange { background: #9a3412; }

        /* Numbered step */
        .step { display: flex; gap: 12px; margin-bottom: 8px; page-break-inside: avoid; }
        .step-num {
            min-width: 26px; height: 26px;
            background: #1d4ed8; color: #fff;
            border-radius: 50%; font-size: 11px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .step-body { flex: 1; padding-top: 3px; }
        .step-body h4 { font-size: 10px; font-weight: bold; color: #1d4ed8; margin-bottom: 3px; }
        .step-body p  { font-size: 9px; color: #374151; line-height: 1.6; }
        .step-body ul { margin-left: 16px; margin-top: 3px; font-size: 9px; color: #374151; line-height: 1.7; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 4px; }
        th { background: #eff6ff; color: #1d4ed8; font-weight: bold; padding: 5px 7px; text-align: left; border: 1px solid #dbeafe; }
        td { padding: 5px 7px; border: 1px solid #e5e7eb; vertical-align: top; line-height: 1.5; }
        tr:nth-child(even) td { background: #f9fafb; }

        /* Example box */
        .example-box {
            background: #fefce8; border-left: 4px solid #eab308;
            padding: 10px 14px; margin: 10px 0; font-size: 9px;
        }
        .example-box .ex-title { font-weight: bold; color: #854d0e; margin-bottom: 6px; font-size: 10px; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-high     { background: #ffedd5; color: #9a3412; }
        .badge-medium   { background: #fef9c3; color: #854d0e; }
        .badge-low      { background: #dcfce7; color: #166534; }

        /* Risk matrix */
        .matrix-crit { background: #fee2e2; }
        .matrix-high { background: #ffedd5; }
        .matrix-med  { background: #fef9c3; }
        .matrix-low  { background: #dcfce7; }

        /* Callout */
        .callout { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 10px 14px; margin: 10px 0; font-size: 9px; }
        .callout-warn { background: #fff7ed; border-color: #fed7aa; }
        .callout-info { background: #eff6ff; border-color: #bfdbfe; }

        /* Industrial insight box */
        .insight { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px; margin: 10px 0; }
        .insight h4 { font-size: 10px; color: #374151; font-weight: bold; margin-bottom: 5px; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; margin-top: 22px; padding-top: 8px; font-size: 8px; color: #9ca3af; display: flex; justify-content: space-between; }
        .page-break { page-break-before: always; }

        /* Sig row */
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 24px; }
        .sig-box { border-top: 1px solid #374151; padding-top: 5px; }
        .sig-box label { font-size: 8px; color: #6b7280; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-inner">
            <div class="logo-area">
                <h1>NOVAREX</h1>
                <p>Health, Safety &amp; Environment Management System</p>
            </div>
            <div class="doc-meta">
                <div class="doc-title">HAZOP Study Procedure</div>
                <div>Document Ref: PRO-HSE-HAZOP-001</div>
                <div>Generated: {{ now()->format('d M Y') }}</div>
                <div>Standard: ISO 31010 | ISO 45001 | ISO 19011</div>
            </div>
        </div>
    </div>

    {{-- COVER BOX --}}
    <div class="cover-box">
        <h2>HAZOP Study Procedure</h2>
        <p style="font-size:9px; color:#374151; margin-bottom:10px;">
            Aligned with ISO 31010 (Risk Assessment Techniques), ISO 45001 (Occupational Health &amp; Safety Management) and ISO 19011 (Auditing Management Systems).
            Applicable to oil &amp; gas facilities, chemical plants, refineries, power generation, and large-scale infrastructure projects.
        </p>
        <div class="cover-grid">
            <div class="cover-field"><label>Document Type</label><span>Process Safety Procedure</span></div>
            <div class="cover-field"><label>Classification</label><span>Controlled — HSE &amp; Operations</span></div>
            <div class="cover-field"><label>Applicable Standard</label><span>ISO 31010:2019 / ISO 45001:2018</span></div>
            <div class="cover-field"><label>Review Frequency</label><span>Annual / Post-Incident</span></div>
            <div class="cover-field"><label>Owner</label><span>HSE Manager</span></div>
            <div class="cover-field"><label>Effective Date</label><span>{{ now()->format('d M Y') }}</span></div>
        </div>
    </div>

    {{-- SECTION 1: OBJECTIVE --}}
    <div class="section">
        <div class="section-title">1. Objective</div>
        <p style="font-size:9.5px; line-height:1.7;">
            To systematically identify hazards and operability issues in process systems using structured guide-word methodology,
            enabling risk quantification and prioritisation of corrective actions before or during plant operations.
            The HAZOP study evaluates each process node against defined design intent, applies structured guide words to identify deviations,
            analyses causes and consequences, and defines controls and actions to reduce risk to ALARP (As Low As Reasonably Practicable) levels.
        </p>
    </div>

    {{-- SECTION 2: SCOPE --}}
    <div class="section">
        <div class="section-title">2. Scope &amp; Applicability</div>
        <p style="font-size:9px; margin-bottom:6px;">This procedure applies to:</p>
        <table>
            <thead><tr><th>Industry / Application</th><th>Applicable Stage</th><th>Typical Node Types</th></tr></thead>
            <tbody>
                <tr><td>Oil &amp; Gas Facilities</td><td>Design (FEED/Detailed) &amp; Operations</td><td>Wells, separators, pipelines, compressors, storage tanks</td></tr>
                <tr><td>Chemical Plants</td><td>Design &amp; Pre-startup review (PSSR)</td><td>Reactors, distillation columns, heat exchangers</td></tr>
                <tr><td>Refineries</td><td>Design, turnaround planning, MOC</td><td>Fractionation units, hydrotreaters, flare systems</td></tr>
                <tr><td>Power Generation</td><td>Design &amp; Major maintenance</td><td>Boilers, turbines, cooling systems, fuel supply</td></tr>
                <tr><td>Infrastructure Projects</td><td>Design phase &amp; construction</td><td>Water systems, HVAC, electrical distribution</td></tr>
            </tbody>
        </table>
    </div>

    {{-- SECTION 3: TEAM --}}
    <div class="section">
        <div class="section-title">3. HAZOP Team Composition</div>
        <table>
            <thead><tr><th>Role</th><th>Responsibility</th><th>Required Qualification</th></tr></thead>
            <tbody>
                <tr><td><strong>HAZOP Chairperson / Facilitator</strong></td><td>Independent study leader, controls methodology, ensures completeness</td><td>Certified HAZOP Leader (IChemE or equivalent), min. 5 years process safety</td></tr>
                <tr><td>Process Engineer</td><td>Provides technical process knowledge and P&amp;ID interpretation</td><td>BSc Chemical/Process Engineering, process safety awareness</td></tr>
                <tr><td>Operations Representative</td><td>Provides operational insight and real-world failure scenarios</td><td>Qualified plant operator, min. 3 years site experience</td></tr>
                <tr><td>Maintenance Engineer</td><td>Identifies equipment failure modes and historical maintenance data</td><td>Mechanical/Electrical engineering background</td></tr>
                <tr><td>Instrumentation Engineer</td><td>Analyses control system deviations, alarms, interlocks</td><td>ISA certified or equivalent, control systems knowledge</td></tr>
                <tr><td>HSE Specialist</td><td>Evaluates safety consequences, regulatory requirements, PPE adequacy</td><td>NEBOSH Diploma / CMIOSH, HAZOP trained</td></tr>
                <tr><td>HAZOP Secretary / Recorder</td><td>Documents all findings, actions and decisions in real time</td><td>Technical background, HAZOP software proficiency</td></tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- SECTION 4: METHODOLOGY --}}
    <div class="section">
        <div class="section-title">4. HAZOP Methodology — 10-Step Procedure</div>

        <div class="step">
            <div class="step-num">1</div>
            <div class="step-body">
                <h4>Define Study Scope &amp; System Boundary</h4>
                <p>Identify the plant section, process unit, or system boundary. Define what is included and excluded. Confirm P&amp;ID revisions are the latest approved issue. Document the current design intent for each section.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">2</div>
            <div class="step-body">
                <h4>Form the HAZOP Team</h4>
                <p>Assemble a multidisciplinary team (Chairperson, Process, Operations, Maintenance, Instrumentation, HSE). Confirm all team members have reviewed the relevant P&amp;IDs and process data before the study commences.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">3</div>
            <div class="step-body">
                <h4>Break the System into Nodes</h4>
                <p>Divide the system into discrete, manageable sections where the process parameters are essentially uniform.</p>
                <ul>
                    <li>Pumps and compressors</li>
                    <li>Pressure vessels and reactors</li>
                    <li>Pipelines and transfer lines</li>
                    <li>Heat exchangers and cooling systems</li>
                    <li>Storage tanks and receivers</li>
                    <li>Control loops and instrumented systems</li>
                </ul>
            </div>
        </div>

        <div class="step">
            <div class="step-num">4</div>
            <div class="step-body">
                <h4>Define Design Intent for Each Node</h4>
                <p>Document the intended operating conditions. Example: <em>"Transfer crude oil at 50 m³/hr from storage tank TK-01 to separator V-01 at 8–10 bar and ambient temperature."</em></p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">5</div>
            <div class="step-body">
                <h4>Apply Guide Words to Parameters</h4>
                <p>Systematically apply each IEC 61882 guide word to each process parameter for the node:</p>
                <ul>
                    <li><strong>Parameters:</strong> Flow, Pressure, Temperature, Level, Composition, Reaction, Phase, Time, Speed, Voltage, Signal</li>
                    <li><strong>Guide Words:</strong> NO/NOT, MORE OF, LESS OF, REVERSE, AS WELL AS, PART OF, OTHER THAN, EARLY, LATE, BEFORE, AFTER</li>
                </ul>
            </div>
        </div>

        <div class="step">
            <div class="step-num">6</div>
            <div class="step-body">
                <h4>Identify Causes of Deviation</h4>
                <ul>
                    <li>Equipment failure (pump, valve, instrument, vessel)</li>
                    <li>Human error (incorrect operation, maintenance error)</li>
                    <li>Control system failure (DCS, PLC, interlock malfunction)</li>
                    <li>External events (power failure, utility failure, extreme weather)</li>
                    <li>Process upsets (raw material variation, reaction runaway)</li>
                </ul>
            </div>
        </div>

        <div class="step">
            <div class="step-num">7</div>
            <div class="step-body">
                <h4>Analyse Consequences</h4>
                <ul>
                    <li><strong>Safety:</strong> Injury, fatality, fire, explosion, toxic release</li>
                    <li><strong>Environmental:</strong> Spill, atmospheric emission, soil contamination</li>
                    <li><strong>Operational:</strong> Production shutdown, equipment damage, quality impact</li>
                    <li><strong>Financial / Reputational:</strong> Asset loss, regulatory penalty, litigation</li>
                </ul>
            </div>
        </div>

        <div class="step">
            <div class="step-num">8</div>
            <div class="step-body">
                <h4>Identify Existing Safeguards &amp; Calculate Initial Risk</h4>
                <p>List all existing protection layers (alarms, interlocks, PSVs, ESD, procedures). Then calculate:</p>
                <ul>
                    <li><strong>Initial Risk Score = L × S × E</strong> where L = Likelihood (1–5), S = Severity (1–5), E = Exposure (1–5). Range: 1–125.</li>
                    <li><strong>RPN = S × O × D</strong> where O = Occurrence, D = Detectability (each 1–5). Range: 1–125.</li>
                </ul>
            </div>
        </div>

        <div class="step">
            <div class="step-num">9</div>
            <div class="step-body">
                <h4>Recommend Actions &amp; Calculate Residual Risk</h4>
                <p>Define engineering controls, administrative controls and maintenance improvements. Calculate:</p>
                <ul>
                    <li><strong>Residual Risk (RR) = IR × (1 − CE%)</strong> where CE = Control Effectiveness (0–100%)</li>
                    <li><strong>Risk Reduction Factor (RRF) = IR ÷ RR</strong> — measures how much controls reduced the risk</li>
                </ul>
                <p style="margin-top:4px;">Target: Residual risk should fall in the Low (1–20) or Medium (21–50) range.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">10</div>
            <div class="step-body">
                <h4>Action Tracking &amp; Close-out Verification</h4>
                <p>Assign risk owner and due date. Track status: <strong>Open → Action Assigned → In Progress → Verification Pending → Closed</strong>. Verify implementation evidence before marking closed. Update risk register and revalidate on schedule.</p>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- SECTION 5: GUIDE WORD TABLE --}}
    <div class="section">
        <div class="section-title">5. HAZOP Guide Words &amp; Parameters Reference (IEC 61882)</div>
        <table>
            <thead>
                <tr>
                    <th>Guide Word</th>
                    <th>Meaning</th>
                    <th>Typical Parameters Applied</th>
                    <th>Example Deviation</th>
                    <th>Common Safeguards</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><strong>NO / NOT</strong></td><td>Complete negation of design intent</td><td>Flow, Reaction, Signal</td><td>No Flow — pump failure, suction blockage</td><td>Low flow alarm, standby pump auto-start</td></tr>
                <tr><td><strong>MORE OF</strong></td><td>Quantitative increase above design</td><td>Flow, Pressure, Temperature, Level</td><td>High Pressure — downstream blockage</td><td>PSV, high pressure alarm, pressure relief</td></tr>
                <tr><td><strong>LESS OF</strong></td><td>Quantitative decrease below design</td><td>Flow, Pressure, Temperature, Level</td><td>Low Flow — partial blockage, pump wear</td><td>Low flow alarm, flow controller</td></tr>
                <tr><td><strong>REVERSE</strong></td><td>Logical opposite of design intent</td><td>Flow, Reaction, Rotation</td><td>Reverse Flow — check valve failure</td><td>Non-return valve, dual check valves</td></tr>
                <tr><td><strong>AS WELL AS</strong></td><td>Additional activity or phase present</td><td>Composition, Phase</td><td>Contaminated stream — cross-connection</td><td>Line segregation, sampling procedure</td></tr>
                <tr><td><strong>PART OF</strong></td><td>Only part of design intent achieved</td><td>Composition, Reaction</td><td>Incomplete reaction — wrong temperature</td><td>Temperature controller, batch timer</td></tr>
                <tr><td><strong>OTHER THAN</strong></td><td>Complete substitution — different material/condition</td><td>Composition, Phase</td><td>Wrong chemical — labelling error</td><td>Interlock, double-check procedure</td></tr>
                <tr><td><strong>EARLY</strong></td><td>Event or action happens before intended time</td><td>Time, Sequence</td><td>Premature batch discharge</td><td>Timer interlock, sequence control</td></tr>
                <tr><td><strong>LATE</strong></td><td>Event or action happens after intended time</td><td>Time, Sequence</td><td>Delayed shutdown signal</td><td>Watchdog timer, redundant initiator</td></tr>
            </tbody>
        </table>
    </div>

    {{-- SECTION 6: REAL INDUSTRIAL EXAMPLES --}}
    <div class="section">
        <div class="section-title green">6. Real Industrial HAZOP Example — Crude Oil Transfer Pump P-101</div>
        <p style="font-size:9px; margin-bottom:6px;">
            <strong>Node:</strong> Pump P-101 — Transfer crude oil at 50 m³/hr from storage tank TK-01 to separator V-01 at 8–10 bar.
        </p>
        <table>
            <thead>
                <tr>
                    <th>Deviation</th>
                    <th>Guide Word</th>
                    <th>Causes</th>
                    <th>Consequences</th>
                    <th>Safeguards</th>
                    <th>L×S×E Score</th>
                    <th>Recommendations</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>No Flow</td>
                    <td>NO</td>
                    <td>Pump failure, power loss, suction blockage</td>
                    <td>Production shutdown, pump overheating</td>
                    <td>Low flow alarm, standby pump</td>
                    <td><span class="badge badge-medium">3×4×4 = 48</span></td>
                    <td>Install auto-start standby pump; add suction strainer differential pressure alarm</td>
                </tr>
                <tr>
                    <td>More Flow</td>
                    <td>MORE OF</td>
                    <td>Control valve failure open, operator error</td>
                    <td>Overflow in separator, process upset</td>
                    <td>Flow controller FIC-101</td>
                    <td><span class="badge badge-medium">3×3×4 = 36</span></td>
                    <td>Install high flow trip (HFSHH-101); review FCV sizing</td>
                </tr>
                <tr>
                    <td>Reverse Flow</td>
                    <td>REVERSE</td>
                    <td>Check valve failure, pump reverse rotation</td>
                    <td>Backflow into tank, contamination, cavitation</td>
                    <td>Non-return valve NRV-101</td>
                    <td><span class="badge badge-medium">2×4×3 = 24</span></td>
                    <td>Add dual check valves; implement quarterly NRV testing</td>
                </tr>
                <tr>
                    <td>High Pressure</td>
                    <td>MORE OF</td>
                    <td>Downstream blockage, discharge valve closed</td>
                    <td>Pipe rupture, leak, fire hazard</td>
                    <td>Pressure relief valve PRV-101</td>
                    <td><span class="badge badge-high">4×5×3 = 60</span></td>
                    <td>Increase PRV inspection frequency; add high pressure alarm PAHH-101</td>
                </tr>
                <tr>
                    <td>Seal Leak / Fire</td>
                    <td>OTHER THAN</td>
                    <td>Mechanical seal failure, corrosion</td>
                    <td>Fire, environmental spill, injury risk</td>
                    <td>Bund wall, gas detectors</td>
                    <td><span class="badge badge-critical">4×5×5 = 100</span></td>
                    <td>Upgrade to SS316 double-mechanical seal; install hydrocarbon gas detector GD-101</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SECTION 7: REACTOR EXAMPLE --}}
    <div class="section">
        <div class="section-title green">7. Real Industrial HAZOP Example — Chemical Reactor R-201 (Exothermic)</div>
        <table>
            <thead>
                <tr>
                    <th>Deviation</th>
                    <th>Causes</th>
                    <th>Consequences</th>
                    <th>Safeguards</th>
                    <th>L×S×E</th>
                    <th>Recommendations</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>High Temperature</td>
                    <td>Cooling failure, cooling water pump trip, sensor error</td>
                    <td>Runaway reaction, explosion risk, vessel rupture</td>
                    <td>Cooling jacket, TAHH alarm, ESD</td>
                    <td><span class="badge badge-critical">4×5×5 = 100</span></td>
                    <td>Install redundant cooling loop; add TSHH independent ESD; SIL 2 assessment required</td>
                </tr>
                <tr>
                    <td>Low Reactant Flow</td>
                    <td>Dosing pump failure, suction strainer blocked</td>
                    <td>Poor reaction, off-spec product, catalyst damage</td>
                    <td>Flow indicator, low flow alarm</td>
                    <td><span class="badge badge-medium">3×3×4 = 36</span></td>
                    <td>Add standby dosing pump; install automatic reactor batch hold on low flow</td>
                </tr>
                <tr>
                    <td>High Pressure</td>
                    <td>Blocked outlet, vent valve failure closed</td>
                    <td>Vessel rupture, toxic gas release</td>
                    <td>PSV (pressure safety valve)</td>
                    <td><span class="badge badge-high">3×5×4 = 60</span></td>
                    <td>Install rupture disk backup; add PAHH trip to close feed valves</td>
                </tr>
                <tr>
                    <td>Wrong Chemical</td>
                    <td>Operator error, mislabelled delivery</td>
                    <td>Toxic gas release, uncontrolled reaction</td>
                    <td>Labelling system, operator procedure</td>
                    <td><span class="badge badge-high">3×5×3 = 45</span></td>
                    <td>Add interlock system preventing incompatible chemical addition; install chemical ID confirmation step</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- SECTION 8: RISK MATRIX --}}
    <div class="section">
        <div class="section-title">8. NOVAREX Quantitative Risk Matrix</div>
        <p style="font-size:9px; margin-bottom:8px;">
            <strong>Formula Used:</strong> Risk Score = Likelihood × Severity × Exposure (L×S×E) | Range: 1–125
        </p>
        <table>
            <thead>
                <tr>
                    <th>Score Range</th>
                    <th>Risk Classification</th>
                    <th>Approval Authority</th>
                    <th>Required Response</th>
                    <th>Target Residual Risk</th>
                </tr>
            </thead>
            <tbody>
                <tr class="matrix-crit">
                    <td><strong>81–125</strong></td>
                    <td><span class="badge badge-critical">Critical</span></td>
                    <td>HSE Manager + Top Management (MD / CEO)</td>
                    <td>Stop work immediately. No resumption without written senior management authorization. SIL assessment required.</td>
                    <td>≤ 20 (Low)</td>
                </tr>
                <tr class="matrix-high">
                    <td><strong>51–80</strong></td>
                    <td><span class="badge badge-high">High</span></td>
                    <td>HSE Manager</td>
                    <td>Immediate corrective actions. Work may continue with enhanced controls. Management review within 24 hours.</td>
                    <td>≤ 50 (Medium)</td>
                </tr>
                <tr class="matrix-med">
                    <td><strong>21–50</strong></td>
                    <td><span class="badge badge-medium">Medium</span></td>
                    <td>HSE Officer</td>
                    <td>Implement additional controls where reasonably practicable. Schedule action within 30 days.</td>
                    <td>≤ 20 (Low)</td>
                </tr>
                <tr class="matrix-low">
                    <td><strong>1–20</strong></td>
                    <td><span class="badge badge-low">Low</span></td>
                    <td>Supervisor</td>
                    <td>Acceptable risk. Maintain existing controls. Monitor periodically. Review annually.</td>
                    <td>Maintain at Low</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:12px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; font-size:9px;">
            <div style="border:1px solid #e5e7eb; border-radius:4px; padding:8px;">
                <strong>Likelihood (L) Scale</strong>
                <ul style="margin-left:14px; margin-top:4px; line-height:1.8;">
                    <li>1 — Rare</li><li>2 — Unlikely</li><li>3 — Possible</li><li>4 — Likely</li><li>5 — Almost Certain</li>
                </ul>
            </div>
            <div style="border:1px solid #e5e7eb; border-radius:4px; padding:8px;">
                <strong>Severity (S) Scale</strong>
                <ul style="margin-left:14px; margin-top:4px; line-height:1.8;">
                    <li>1 — Negligible</li><li>2 — Minor Injury</li><li>3 — MTC / Reportable</li><li>4 — Major Injury</li><li>5 — Fatality / Catastrophic</li>
                </ul>
            </div>
            <div style="border:1px solid #e5e7eb; border-radius:4px; padding:8px;">
                <strong>Exposure (E) Scale</strong>
                <ul style="margin-left:14px; margin-top:4px; line-height:1.8;">
                    <li>1 — Annual</li><li>2 — Quarterly</li><li>3 — Monthly</li><li>4 — Weekly</li><li>5 — Daily / Continuous</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- SECTION 9: INDUSTRIAL INSIGHT --}}
    <div class="section">
        <div class="section-title gray">9. Real Industrial HAZOP Insight</div>
        <div class="insight">
            <h4>Scale of Industrial HAZOP Studies (Oil &amp; Gas)</h4>
            <table>
                <thead><tr><th>Parameter</th><th>Typical Range</th><th>Notes</th></tr></thead>
                <tbody>
                    <tr><td>Duration per Node</td><td>30–120 minutes</td><td>Complex nodes may take longer</td></tr>
                    <tr><td>Full Plant HAZOP Duration</td><td>2–8 weeks</td><td>Depends on plant complexity and team efficiency</td></tr>
                    <tr><td>Number of Findings (medium plant)</td><td>200–800 nodes</td><td>Refineries can exceed 1,000+ deviations</td></tr>
                    <tr><td>Typical Team Size</td><td>5–10 people</td><td>Including facilitator, engineers, operations, HSE</td></tr>
                    <tr><td>Documentation</td><td>Risk register, action tracker, SIL study trigger</td><td>All findings must be formally recorded and tracked</td></tr>
                    <tr><td>Regulatory Requirement</td><td>Mandatory for COMAH / PSM sites</td><td>Required by UK COMAH Regulations, US OSHA PSM (29 CFR 1910.119)</td></tr>
                </tbody>
            </table>
        </div>

        <div class="callout callout-warn" style="margin-top:10px;">
            <strong>Important — SIL (Safety Integrity Level) Triggers:</strong><br>
            When a HAZOP identifies a safeguard that requires a Safety Instrumented Function (SIF) to achieve tolerable risk,
            a full SIL determination study (IEC 61511) must be initiated. Critical nodes with initial risk ≥ 81 should
            automatically trigger a SIL assessment review.
        </div>

        <div class="callout callout-info" style="margin-top:8px;">
            <strong>HAZOP vs. HAZID:</strong><br>
            HAZID (Hazard Identification) is a simpler qualitative technique (L×S, 2-variable) used in early design stages or for non-process hazards.
            HAZOP (Hazard &amp; Operability Study) is the full quantitative technique (L×S×E, 3-variable + RPN) used for detailed process safety analysis.
            Both are implemented in the NOVAREX HSE Management System.
        </div>
    </div>

    {{-- SIGNATURES --}}
    <div class="sig-row">
        <div class="sig-box">
            <label>Prepared By (HSE Department)</label><br><br><br>
            <span style="font-size:9px;">Name &amp; Signature</span>
            <div style="font-size:8px; color:#6b7280; margin-top:2px;">Date: ___________________</div>
        </div>
        <div class="sig-box">
            <label>Reviewed By (HSE Manager)</label><br><br><br>
            <span style="font-size:9px;">Name &amp; Signature</span>
            <div style="font-size:8px; color:#6b7280; margin-top:2px;">Date: ___________________</div>
        </div>
        <div class="sig-box">
            <label>Approved By (Managing Director)</label><br><br><br>
            <span style="font-size:9px;">Name &amp; Signature</span>
            <div style="font-size:8px; color:#6b7280; margin-top:2px;">Date: ___________________</div>
        </div>
    </div>

    <div class="footer">
        <span>NOVAREX — PRO-HSE-HAZOP-001 | HAZOP Study Procedure</span>
        <span>ISO 31010:2019 | ISO 45001:2018 | IEC 61882 | ISO 19011:2018</span>
        <span>Generated: {{ now()->format('d M Y') }}</span>
    </div>

</div>
</body>
</html>
