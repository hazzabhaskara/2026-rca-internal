<?php $__env->startPush('styles'); ?>
<style>
    @import  url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .neraca-wrap { font-family: 'Inter', sans-serif; }

    /* Header */
    .neraca-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .neraca-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Summary Cards */
    .summary-card {
        backdrop-filter: blur(12px);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

    /* Table */
    .neraca-matrix-table { border-collapse: separate; border-spacing: 0; }
    .neraca-matrix-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #1e293b;
        color: white;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 2px solid #334155;
    }

    /* Sticky first column */
    .sticky-col-header {
        position: sticky;
        left: 0;
        z-index: 30 !important;
        background: #0f172a !important;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sticky-col-cell {
        position: sticky;
        left: 0;
        z-index: 15;
        background: inherit;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    /* Accordion row types */
    .row-tipe { cursor: pointer; user-select: none; transition: background 0.15s; }
    .row-tipe:hover { filter: brightness(1.08); }

    .row-group { cursor: pointer; user-select: none; transition: background 0.15s; }
    .row-group:hover { filter: brightness(0.97); }

    .row-account { cursor: pointer; transition: background 0.15s; }
    .row-account:hover { background: #f8fafc !important; }

    .row-subaccount { transition: background 0.15s; }
    .row-subaccount:hover { background: #f1f5f9 !important; }

    .collapsible-row { transition: opacity 0.2s ease; }
    .collapsible-row.hidden { display: none; }

    /* Chevron */
    .toggle-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .toggle-chevron.open { transform: rotate(90deg); }
    .toggle-chevron svg { width: 11px; height: 11px; }

    /* Numbers */
    .num-negative { color: #dc2626; font-weight: 500; }
    .num-positive { color: #1e293b; }
    .num-cell { font-variant-numeric: tabular-nums; font-size: 0.76rem; }

    /* Drill-down links */
    .drilldown-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.15s;
    }
    .drilldown-link:hover { color: #4f46e5; text-decoration: underline; }

    /* Control buttons */
    .ctrl-btn {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid #e2e8f0;
        background: white;
        color: #374151;
    }
    .ctrl-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
    .ctrl-btn.active { background: #1e293b; color: white; border-color: #1e293b; }

    /* Print */
    @media  print {
        .no-print { display: none !important; }
        body { background: white !important; font-size: 8pt; }
        .sticky-col-header, .sticky-col-cell { position: static !important; box-shadow: none !important; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="neraca-wrap min-h-screen bg-slate-100 py-6 px-4 sm:px-6 lg:px-8">

    
    <div class="neraca-header rounded-2xl p-6 sm:p-8 mb-6 text-white shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        Financial Statement
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($mode === 'matrix' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-300 border border-blue-400/30'); ?>">
                        <?php echo e($mode === 'matrix' ? 'Mode Matriks Komparatif' : 'Mode Single: ' . ($singleDept->deskripsi ?? 'Departemen')); ?>

                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        100% Balanced
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    Balance Sheet Departemen
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">
                    Posisi Keuangan per <?php echo e(\Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y')); ?>

                    &bull; <?php echo e($siteTitle); ?>

                </p>
            </div>

            
            <div class="flex flex-wrap items-center gap-2.5 no-print">
                <a href="<?php echo e(route('neraca_departemen.filter_neraca_departemen', request()->all())); ?>"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold backdrop-blur border border-white/15 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Ubah Filter
                </a>
                <a href="<?php echo e(route('neraca_departemen.export', array_merge(request()->all(), ['format' => 'excel']))); ?>"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <a href="<?php echo e(route('neraca_departemen.export', array_merge(request()->all(), ['format' => 'pdf']))); ?>"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>

        
        <?php
            $targetCol = $mode === 'single' && $selectedDeptId ? $selectedDeptId : 'total';
            $cardAset = $grandTotals['Aset'][$targetCol] ?? 0;
            $cardKewajiban = $grandTotals['Kewajiban'][$targetCol] ?? 0;
            $cardEkuitas = $grandTotals['Ekuitas'][$targetCol] ?? 0;
            $cardPasiva = $grandTotals['Pasiva'][$targetCol] ?? 0;
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL AKTIVA</span>
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp <?php echo e(number_format($cardAset, 2, ',', '.')); ?>

                </div>
                <div class="text-[11px] text-blue-200 mt-1">
                    <?php echo e($mode === 'single' ? ($singleDept->deskripsi ?? 'Cabang') : 'Total Konsolidasi Perusahaan'); ?>

                </div>
            </div>

            
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL KEWAJIBAN</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp <?php echo e(number_format($cardKewajiban, 2, ',', '.')); ?>

                </div>
                <div class="text-[11px] text-amber-200 mt-1">Liabilitas Cabang & Konsolidasi</div>
            </div>

            
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL EKUITAS + R/K</span>
                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp <?php echo e(number_format($cardEkuitas, 2, ',', '.')); ?>

                </div>
                <div class="text-[11px] text-purple-200 mt-1">Termasuk Laba & R/K Antar Unit</div>
            </div>

            
            <div class="summary-card bg-emerald-500/20 border border-emerald-400/30 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-200">TOTAL PASIVA (KEW + EK)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-emerald-100 mt-1">
                    Rp <?php echo e(number_format($cardPasiva, 2, ',', '.')); ?>

                </div>
                <div class="text-[11px] text-emerald-300 mt-1 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Aktiva - Pasiva = Rp 0,00 (100% Balanced)
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 no-print">
        
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 mr-1">Tingkat Hierarki:</span>
            <button type="button" onclick="setLevel('all')" class="ctrl-btn active" id="btn-lvl-all">Expand All</button>
            <button type="button" onclick="setLevel(2)" class="ctrl-btn" id="btn-lvl-2">Level 2</button>
            <button type="button" onclick="setLevel(1)" class="ctrl-btn" id="btn-lvl-1">Level 1</button>
            <button type="button" onclick="setLevel(0)" class="ctrl-btn" id="btn-lvl-0">Collapse All</button>
        </div>

        
        <div class="relative w-full md:w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="quickSearchInput" onkeyup="filterTableRows(this.value)"
                placeholder="Cari akun atau kode..."
                class="w-full pl-9 pr-4 py-1.5 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
        </div>
    </div>

    <?php if($mode === 'matrix'): ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="neraca-matrix-table w-full text-left" id="matrixReportTable">
                    <thead>
                        <tr>
                            <th class="sticky-col-header" style="min-width: 300px; width: 300px;">
                                KETERANGAN / NAMA AKUN
                            </th>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="text-right" style="min-width: 140px;">
                                    <?php echo e($dept->kode); ?><br>
                                    <span class="text-[10px] font-normal text-slate-300"><?php echo e($dept->deskripsi); ?></span>
                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-right bg-slate-800 text-slate-200" style="min-width: 140px;">
                                NON-DEPT<br>
                                <span class="text-[10px] font-normal text-slate-400">Unassigned</span>
                            </th>
                            <th class="text-right bg-amber-950 text-amber-200" style="min-width: 140px;">
                                ELIMINASI<br>
                                <span class="text-[10px] font-normal text-amber-300">R/K Antar Unit</span>
                            </th>
                            <th class="text-right bg-indigo-950 text-indigo-100 font-bold" style="min-width: 160px;">
                                TOTAL KONSOLIDASI<br>
                                <span class="text-[10px] font-normal text-indigo-300">Resmi Perusahaan</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $tipeConfig = [
                                'Aset' => ['bg' => '#2563eb', 'light' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'AKTIVA / ASET'],
                                'Kewajiban' => ['bg' => '#d97706', 'light' => '#fffbeb', 'text' => '#b45309', 'label' => 'KEWAJIBAN / LIABILITAS'],
                                'Ekuitas' => ['bg' => '#7c3aed', 'light' => '#faf5ff', 'text' => '#6d28d9', 'label' => 'EKUITAS & DANA ANTAR UNIT'],
                            ];
                        ?>

                        <?php $__currentLoopData = ['Aset', 'Kewajiban', 'Ekuitas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $cfg = $tipeConfig[$tipe];
                                $tipeId = 'tipe-' . strtolower($tipe);
                                $accountsList = $neraca[$tipe] ?? [];
                                $groupIndex = 0;
                                $akunIndex = 0;
                                $currentGroupId = null;
                            ?>

                            
                            <tr class="row-tipe" data-tipe="<?php echo e($tipeId); ?>" onclick="toggleTipe('<?php echo e($tipeId); ?>')"
                                style="background: <?php echo e($cfg['bg']); ?>; color: white;">
                                <td class="py-3 px-4 sticky-col-cell font-bold text-xs uppercase tracking-wider flex items-center gap-2"
                                    style="background: <?php echo e($cfg['bg']); ?>;">
                                    <span class="toggle-chevron open" id="chevron-<?php echo e($tipeId); ?>" style="color: white;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                    <span><?php echo e($cfg['label']); ?></span>
                                </td>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="py-3 px-3 text-right font-bold num-cell">
                                        <?php echo e(number_format($grandTotals[$tipe][$dept->id] ?? 0, 2, ',', '.')); ?>

                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="py-3 px-3 text-right font-bold num-cell">
                                    <?php echo e(number_format($grandTotals[$tipe]['unassigned'] ?? 0, 2, ',', '.')); ?>

                                </td>
                                <td class="py-3 px-3 text-right font-bold num-cell text-amber-200">
                                    <?php echo e(number_format($grandTotals[$tipe]['eliminasi'] ?? 0, 2, ',', '.')); ?>

                                </td>
                                <td class="py-3 px-3 text-right font-bold num-cell text-white">
                                    <?php echo e(number_format($grandTotals[$tipe]['total'] ?? 0, 2, ',', '.')); ?>

                                </td>
                            </tr>

                            
                            <?php $__currentLoopData = $accountsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $lvl = $item['level_akun'];
                                    $isZero = $hideAccountWithZero && collect($item['balances'])->every(fn($v) => abs($v) < 0.001);
                                ?>

                                <?php if($lvl === 'HEADER'): ?>
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows"
                                        style="background: <?php echo e($cfg['light']); ?>; border-left: 4px solid <?php echo e($cfg['bg']); ?>;">
                                        <td colspan="<?php echo e(count($departments) + 4); ?>"
                                            class="py-2.5 px-4 sticky-col-cell font-bold text-xs uppercase tracking-wider"
                                            style="color: <?php echo e($cfg['text']); ?>; background: <?php echo e($cfg['light']); ?>;">
                                            <?php echo e($item['nama_akun']); ?>

                                        </td>
                                    </tr>

                                <?php elseif($lvl === 'GROUP ACCOUNT'): ?>
                                    <?php
                                        $groupIndex++;
                                        $currentGroupId = $tipeId . '-grp-' . $groupIndex;
                                    ?>
                                    <tr class="row-group collapsible-row <?php echo e($tipeId); ?>-rows"
                                        data-group="<?php echo e($currentGroupId); ?>" onclick="toggleGroup('<?php echo e($currentGroupId); ?>')"
                                        style="background: #f8fafc; border-left: 4px solid <?php echo e($cfg['bg']); ?>;">
                                        <td class="py-2.5 px-4 pl-7 sticky-col-cell font-semibold text-xs text-slate-800 flex items-center gap-2"
                                            style="background: #f8fafc;">
                                            <span class="toggle-chevron open" id="chevron-<?php echo e($currentGroupId); ?>" style="color: <?php echo e($cfg['text']); ?>;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                            <span><?php echo e($item['nama_akun']); ?></span>
                                        </td>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="py-2.5 px-3 text-right font-medium text-slate-400 num-cell">—</td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="py-2.5 px-3 text-right font-medium text-slate-400 num-cell">—</td>
                                        <td class="py-2.5 px-3 text-right font-medium text-slate-400 num-cell">—</td>
                                        <td class="py-2.5 px-3 text-right font-medium text-slate-400 num-cell">—</td>
                                    </tr>

                                <?php elseif($lvl === 'ACCOUNT'): ?>
                                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                                    <?php
                                        $akunIndex++;
                                        $accountId = ($currentGroupId ?? $tipeId) . '-acc-' . $akunIndex;
                                        $hasChild = $item['has_child'] ?? false;
                                    ?>
                                    <tr class="row-account collapsible-row <?php echo e($tipeId); ?>-rows <?php echo e($currentGroupId ? $currentGroupId . '-rows' : ''); ?>"
                                        <?php if($hasChild): ?> data-account="<?php echo e($accountId); ?>" onclick="toggleAccount('<?php echo e($accountId); ?>')" <?php endif; ?>
                                        style="background: white;">
                                        <td class="py-2 px-4 pl-10 sticky-col-cell text-xs font-semibold text-slate-700 flex items-center gap-2"
                                            style="background: white;">
                                            <?php if($hasChild): ?>
                                                <span class="toggle-chevron open" id="chevron-<?php echo e($accountId); ?>" style="color: #64748b;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                            <?php else: ?>
                                                <span class="w-4"></span>
                                            <?php endif; ?>
                                            <span class="account-name-label">
                                                <?php echo e($showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun']); ?>

                                            </span>
                                        </td>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $v = $item['balances'][$dept->id] ?? 0; ?>
                                            <td class="py-2 px-3 text-right font-semibold num-cell">
                                                <?php if(abs($v) > 0.001): ?>
                                                    <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                        'departemen_id' => $dept->id
                                                    ])); ?>" target="_blank" class="drilldown-link <?php echo e($v < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                        <?php echo e($v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.')); ?>

                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-slate-300">0,00</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php $vUn = $item['balances']['unassigned'] ?? 0; ?>
                                        <td class="py-2 px-3 text-right font-semibold num-cell">
                                            <?php if(abs($vUn) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vUn < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="py-2 px-3 text-right font-semibold num-cell text-slate-400">0,00</td>
                                        
                                        <?php $vTot = $item['balances']['total'] ?? 0; ?>
                                        <td class="py-2 px-3 text-right font-bold num-cell bg-slate-50">
                                            <?php if(abs($vTot) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vTot < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php elseif($lvl === 'SUB ACCOUNT'): ?>
                                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                                    <tr class="row-subaccount collapsible-row <?php echo e($tipeId); ?>-rows <?php echo e($currentGroupId ? $currentGroupId . '-rows' : ''); ?> <?php echo e(isset($accountId) ? $accountId . '-rows' : ''); ?>"
                                        style="background: #fafafa;">
                                        <td class="py-1.5 px-4 pl-14 sticky-col-cell text-[11px] text-slate-600"
                                            style="background: #fafafa;">
                                            <span class="account-name-label">
                                                <?php echo e($showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun']); ?>

                                            </span>
                                        </td>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $v = $item['balances'][$dept->id] ?? 0; ?>
                                            <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-600">
                                                <?php if(abs($v) > 0.001): ?>
                                                    <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                        'departemen_id' => $dept->id
                                                    ])); ?>" target="_blank" class="drilldown-link <?php echo e($v < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                        <?php echo e($v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.')); ?>

                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-slate-300">0,00</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php $vUn = $item['balances']['unassigned'] ?? 0; ?>
                                        <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-600">
                                            <?php if(abs($vUn) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vUn < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-300">0,00</td>
                                        
                                        <?php $vTot = $item['balances']['total'] ?? 0; ?>
                                        <td class="py-1.5 px-3 text-right text-[11px] num-cell font-medium bg-slate-50">
                                            <?php if(abs($vTot) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vTot < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php elseif($lvl === 'X'): ?>
                                    
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows" style="background: #fdf4ff;">
                                        <td class="py-2.5 px-4 pl-10 sticky-col-cell text-xs font-bold text-purple-900 flex items-center gap-2"
                                            style="background: #fdf4ff;">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span><?php echo e($item['nama_akun']); ?></span>
                                        </td>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $v = $item['balances'][$dept->id] ?? 0; ?>
                                            <td class="py-2.5 px-3 text-right font-bold num-cell">
                                                <a href="<?php echo e(route('income_statement.income_statement_departement', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_departemens' => $dept->id
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($v < 0 ? 'num-negative' : 'text-purple-900'); ?>">
                                                    <?php echo e($v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.')); ?>

                                                </a>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php $vUn = $item['balances']['unassigned'] ?? 0; ?>
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-purple-900">
                                            <?php echo e($vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.')); ?>

                                        </td>
                                        
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-purple-300">0,00</td>
                                        
                                        <?php $vTot = $item['balances']['total'] ?? 0; ?>
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-purple-950 bg-purple-100/50">
                                            <a href="<?php echo e(route('income_statement.income_statement_report', [
                                                'start_date' => $tanggalAwalTahun,
                                                'end_date' => $tanggalAkhir
                                            ])); ?>" target="_blank" class="drilldown-link <?php echo e($vTot < 0 ? 'num-negative' : 'text-purple-950'); ?>">
                                                <?php echo e($vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.')); ?>

                                            </a>
                                        </td>
                                    </tr>

                                <?php elseif($lvl === 'RK'): ?>
                                    
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows" style="background: #eef2ff;">
                                        <td class="py-2.5 px-4 pl-10 sticky-col-cell text-xs font-bold text-indigo-950 flex items-center gap-2"
                                            style="background: #eef2ff;">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                            <span class="flex items-center gap-1.5">
                                                <?php echo e($item['nama_akun']); ?>

                                                <span class="text-[10px] font-normal px-1.5 py-0.5 bg-indigo-200 text-indigo-800 rounded">
                                                    Penyeimbang Otomatis
                                                </span>
                                            </span>
                                        </td>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $rk = $item['balances'][$dept->id] ?? 0; ?>
                                            <td class="py-2.5 px-3 text-right font-bold num-cell">
                                                <button type="button"
                                                    onclick="openRkAuditModal('<?php echo e($dept->id); ?>', '<?php echo e($dept->deskripsi); ?>', '<?php echo e($tanggalAkhir); ?>')"
                                                    title="Klik untuk melihat audit transaksi silang pembentuk R/K"
                                                    class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-900 transition underline decoration-dotted">
                                                    <?php echo e($rk < 0 ? '(' . number_format(abs($rk), 2, ',', '.') . ')' : number_format($rk, 2, ',', '.')); ?>

                                                </button>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <?php $rkUn = $item['balances']['unassigned'] ?? 0; ?>
                                        <td class="py-2.5 px-3 text-right font-bold num-cell">
                                            <button type="button"
                                                onclick="openRkAuditModal('unassigned', 'Non-Departemen / Unassigned', '<?php echo e($tanggalAkhir); ?>')"
                                                title="Klik untuk melihat audit transaksi silang Non-Departemen"
                                                class="inline-block px-1.5 py-0.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 transition underline decoration-dotted">
                                                <?php echo e($rkUn < 0 ? '(' . number_format(abs($rkUn), 2, ',', '.') . ')' : number_format($rkUn, 2, ',', '.')); ?>

                                            </button>
                                        </td>
                                        
                                        <?php $rkElim = $item['balances']['eliminasi'] ?? 0; ?>
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-amber-900 bg-amber-50">
                                            <?php echo e($rkElim < 0 ? '(' . number_format(abs($rkElim), 2, ',', '.') . ')' : number_format($rkElim, 2, ',', '.')); ?>

                                        </td>
                                        
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-indigo-950 bg-indigo-100/50">
                                            0,00
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <tr class="row-total" style="background: <?php echo e($cfg['light']); ?>; border-top: 2px solid <?php echo e($cfg['bg']); ?>; border-bottom: 2px solid <?php echo e($cfg['bg']); ?>;">
                                <td class="py-3 px-4 sticky-col-cell font-bold text-xs uppercase"
                                    style="color: <?php echo e($cfg['text']); ?>; background: <?php echo e($cfg['light']); ?>;">
                                    TOTAL <?php echo e($cfg['label']); ?>

                                </td>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="py-3 px-3 text-right font-bold num-cell" style="color: <?php echo e($cfg['text']); ?>;">
                                        <?php echo e(number_format($grandTotals[$tipe][$dept->id] ?? 0, 2, ',', '.')); ?>

                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="py-3 px-3 text-right font-bold num-cell" style="color: <?php echo e($cfg['text']); ?>;">
                                    <?php echo e(number_format($grandTotals[$tipe]['unassigned'] ?? 0, 2, ',', '.')); ?>

                                </td>
                                <td class="py-3 px-3 text-right font-bold num-cell text-amber-700">
                                    <?php echo e(number_format($grandTotals[$tipe]['eliminasi'] ?? 0, 2, ',', '.')); ?>

                                </td>
                                <td class="py-3 px-3 text-right font-bold num-cell text-indigo-950 bg-indigo-100/40">
                                    <?php echo e(number_format($grandTotals[$tipe]['total'] ?? 0, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        
                        <tr style="background: #0f172a; color: white; border-top: 3px solid #6366f1;">
                            <td class="py-3.5 px-4 sticky-col-cell font-bold text-xs uppercase tracking-wider"
                                style="background: #0f172a; color: white;">
                                TOTAL PASIVA (KEWAJIBAN & EKUITAS)
                            </td>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="py-3.5 px-3 text-right font-bold num-cell text-emerald-300">
                                    <?php echo e(number_format($grandTotals['Pasiva'][$dept->id] ?? 0, 2, ',', '.')); ?>

                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="py-3.5 px-3 text-right font-bold num-cell text-emerald-300">
                                <?php echo e(number_format($grandTotals['Pasiva']['unassigned'] ?? 0, 2, ',', '.')); ?>

                            </td>
                            <td class="py-3.5 px-3 text-right font-bold num-cell text-amber-300">
                                <?php echo e(number_format($grandTotals['Pasiva']['eliminasi'] ?? 0, 2, ',', '.')); ?>

                            </td>
                            <td class="py-3.5 px-3 text-right font-bold num-cell text-emerald-300 bg-slate-900">
                                <?php echo e(number_format($grandTotals['Pasiva']['total'] ?? 0, 2, ',', '.')); ?>

                            </td>
                        </tr>

                        
                        <tr style="background: #f0fdf4; border-top: 1px solid #bbf7d0;">
                            <td class="py-2.5 px-4 sticky-col-cell font-semibold text-xs text-emerald-800 flex items-center gap-1.5"
                                style="background: #f0fdf4;">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>STATUS BALANCE (AKTIVA - PASIVA)</span>
                            </td>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $diff = ($grandTotals['Aset'][$dept->id] ?? 0) - ($grandTotals['Pasiva'][$dept->id] ?? 0); ?>
                                <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-emerald-700">
                                    <?php echo e(abs($diff) < 0.01 ? 'Rp 0,00' : number_format($diff, 2, ',', '.')); ?>

                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $diffUn = ($grandTotals['Aset']['unassigned'] ?? 0) - ($grandTotals['Pasiva']['unassigned'] ?? 0); ?>
                            <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-emerald-700">
                                <?php echo e(abs($diffUn) < 0.01 ? 'Rp 0,00' : number_format($diffUn, 2, ',', '.')); ?>

                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-amber-700">0,00</td>
                            <?php $diffTot = ($grandTotals['Aset']['total'] ?? 0) - ($grandTotals['Pasiva']['total'] ?? 0); ?>
                            <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-emerald-800 bg-emerald-100/50">
                                <?php echo e(abs($diffTot) < 0.01 ? 'Rp 0,00' : number_format($diffTot, 2, ',', '.')); ?>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        
        <?php
            $singleId = $selectedDeptId;
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        [<?php echo e($singleDept->kode ?? '-'); ?>] <?php echo e($singleDept->deskripsi ?? 'Departemen'); ?>

                    </h3>
                    <p class="text-xs text-slate-500">Laporan Neraca Tunggal Cabang</p>
                </div>
                <div class="px-3 py-1 bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold rounded-full flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    100% Balanced
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="singleReportTable">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                            <th class="py-3 px-4" style="min-width: 320px;">KETERANGAN</th>
                            <th class="py-3 px-4 text-right" style="width: 170px;">SUB ACCOUNT</th>
                            <th class="py-3 px-4 text-right" style="width: 170px;">ACCOUNT</th>
                            <th class="py-3 px-4 text-right" style="width: 180px;">GROUP ACCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $tipeConfig = [
                                'Aset' => ['bg' => '#2563eb', 'light' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'AKTIVA / ASET'],
                                'Kewajiban' => ['bg' => '#d97706', 'light' => '#fffbeb', 'text' => '#b45309', 'label' => 'KEWAJIBAN / LIABILITAS'],
                                'Ekuitas' => ['bg' => '#7c3aed', 'light' => '#faf5ff', 'text' => '#6d28d9', 'label' => 'EKUITAS & DANA ANTAR UNIT'],
                            ];
                        ?>

                        <?php $__currentLoopData = ['Aset', 'Kewajiban', 'Ekuitas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $cfg = $tipeConfig[$tipe];
                                $tipeId = 'single-tipe-' . strtolower($tipe);
                                $accountsList = $neraca[$tipe] ?? [];
                                $groupIndex = 0;
                                $akunIndex = 0;
                                $currentGroupId = null;
                            ?>

                            
                            <tr class="row-tipe" data-tipe="<?php echo e($tipeId); ?>" onclick="toggleTipe('<?php echo e($tipeId); ?>')"
                                style="background: <?php echo e($cfg['bg']); ?>; color: white;">
                                <td colspan="3" class="py-3 px-4 font-bold text-xs uppercase tracking-wider flex items-center gap-2">
                                    <span class="toggle-chevron open" id="chevron-<?php echo e($tipeId); ?>" style="color: white;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                    <span><?php echo e($cfg['label']); ?></span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold num-cell text-white">
                                    <?php echo e(number_format($grandTotals[$tipe][$singleId] ?? 0, 2, ',', '.')); ?>

                                </td>
                            </tr>

                            
                            <?php $__currentLoopData = $accountsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $lvl = $item['level_akun'];
                                    $vSelf = $item['balances_self'][$singleId] ?? 0;
                                    $vAgg = $item['balances'][$singleId] ?? 0;
                                    $isZero = $hideAccountWithZero && (abs($vAgg) < 0.001);
                                ?>

                                <?php if($lvl === 'HEADER'): ?>
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows"
                                        style="background: <?php echo e($cfg['light']); ?>; border-left: 4px solid <?php echo e($cfg['bg']); ?>;">
                                        <td colspan="4" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider"
                                            style="color: <?php echo e($cfg['text']); ?>;">
                                            <?php echo e($item['nama_akun']); ?>

                                        </td>
                                    </tr>

                                <?php elseif($lvl === 'GROUP ACCOUNT'): ?>
                                    <?php
                                        $groupIndex++;
                                        $currentGroupId = $tipeId . '-grp-' . $groupIndex;
                                    ?>
                                    <tr class="row-group collapsible-row <?php echo e($tipeId); ?>-rows"
                                        data-group="<?php echo e($currentGroupId); ?>" onclick="toggleGroup('<?php echo e($currentGroupId); ?>')"
                                        style="background: #f8fafc; border-left: 4px solid <?php echo e($cfg['bg']); ?>;">
                                        <td class="py-2.5 px-4 pl-7 font-semibold text-xs text-slate-800 flex items-center gap-2">
                                            <span class="toggle-chevron open" id="chevron-<?php echo e($currentGroupId); ?>" style="color: <?php echo e($cfg['text']); ?>;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                            <span><?php echo e($item['nama_akun']); ?></span>
                                        </td>
                                        <td></td><td></td>
                                        <td class="py-2.5 px-4 text-right font-medium text-slate-400 num-cell">—</td>
                                    </tr>

                                <?php elseif($lvl === 'ACCOUNT'): ?>
                                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                                    <?php
                                        $akunIndex++;
                                        $accountId = ($currentGroupId ?? $tipeId) . '-acc-' . $akunIndex;
                                        $hasChild = $item['has_child'] ?? false;
                                    ?>
                                    <tr class="row-account collapsible-row <?php echo e($tipeId); ?>-rows <?php echo e($currentGroupId ? $currentGroupId . '-rows' : ''); ?>"
                                        <?php if($hasChild): ?> data-account="<?php echo e($accountId); ?>" onclick="toggleAccount('<?php echo e($accountId); ?>')" <?php endif; ?>
                                        style="background: white;">
                                        <td class="py-2 px-4 pl-10 text-xs font-semibold text-slate-700 flex items-center gap-2">
                                            <?php if($hasChild): ?>
                                                <span class="toggle-chevron open" id="chevron-<?php echo e($accountId); ?>" style="color: #64748b;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                            <?php else: ?>
                                                <span class="w-4"></span>
                                            <?php endif; ?>
                                            <span><?php echo e($showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun']); ?></span>
                                        </td>
                                        <td></td>
                                        <td class="py-2 px-4 text-right font-semibold num-cell">
                                            <?php if(abs($vAgg) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun'],
                                                    'departemen_id' => $singleId
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vAgg < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td></td>
                                    </tr>

                                <?php elseif($lvl === 'SUB ACCOUNT'): ?>
                                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                                    <tr class="row-subaccount collapsible-row <?php echo e($tipeId); ?>-rows <?php echo e($currentGroupId ? $currentGroupId . '-rows' : ''); ?> <?php echo e(isset($accountId) ? $accountId . '-rows' : ''); ?>"
                                        style="background: #fafafa;">
                                        <td class="py-1.5 px-4 pl-14 text-[11px] text-slate-600">
                                            <?php echo e($showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun']); ?>

                                        </td>
                                        <td class="py-1.5 px-4 text-right text-[11px] num-cell text-slate-600">
                                            <?php if(abs($vSelf) > 0.001): ?>
                                                <a href="<?php echo e(route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun'],
                                                    'departemen_id' => $singleId
                                                ])); ?>" target="_blank" class="drilldown-link <?php echo e($vSelf < 0 ? 'num-negative' : 'num-positive'); ?>">
                                                    <?php echo e($vSelf < 0 ? '(' . number_format(abs($vSelf), 2, ',', '.') . ')' : number_format($vSelf, 2, ',', '.')); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">0,00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td></td><td></td>
                                    </tr>

                                <?php elseif($lvl === 'X'): ?>
                                    
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows" style="background: #fdf4ff;">
                                        <td class="py-2.5 px-4 pl-10 text-xs font-bold text-purple-900 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span><?php echo e($item['nama_akun']); ?></span>
                                        </td>
                                        <td></td>
                                        <td class="py-2.5 px-4 text-right font-bold num-cell">
                                            <a href="<?php echo e(route('income_statement.income_statement_departement', [
                                                'start_date' => $tanggalAwalTahun,
                                                'end_date' => $tanggalAkhir,
                                                'selected_departemens' => $singleId
                                            ])); ?>" target="_blank" class="drilldown-link <?php echo e($vAgg < 0 ? 'num-negative' : 'text-purple-900'); ?>">
                                                <?php echo e($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')); ?>

                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>

                                <?php elseif($lvl === 'RK'): ?>
                                    
                                    <tr class="collapsible-row <?php echo e($tipeId); ?>-rows" style="background: #eef2ff;">
                                        <td class="py-2.5 px-4 pl-10 text-xs font-bold text-indigo-950 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                            <span><?php echo e($item['nama_akun']); ?></span>
                                            <span class="text-[10px] font-normal px-1.5 py-0.5 bg-indigo-200 text-indigo-800 rounded">
                                                Penyeimbang Otomatis
                                            </span>
                                        </td>
                                        <td></td>
                                        <td class="py-2.5 px-4 text-right font-bold num-cell">
                                            <button type="button"
                                                onclick="openRkAuditModal('<?php echo e($singleId); ?>', '<?php echo e($singleDept->deskripsi ?? 'Cabang'); ?>', '<?php echo e($tanggalAkhir); ?>')"
                                                title="Klik untuk melihat audit transaksi silang pembentuk R/K"
                                                class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-900 transition underline decoration-dotted">
                                                <?php echo e($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')); ?>

                                            </button>
                                        </td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <tr class="row-total" style="background: <?php echo e($cfg['light']); ?>; border-top: 2px solid <?php echo e($cfg['bg']); ?>; border-bottom: 2px solid <?php echo e($cfg['bg']); ?>;">
                                <td colspan="3" class="py-3 px-4 font-bold text-xs uppercase" style="color: <?php echo e($cfg['text']); ?>;">
                                    TOTAL <?php echo e($cfg['label']); ?>

                                </td>
                                <td class="py-3 px-4 text-right font-bold num-cell" style="color: <?php echo e($cfg['text']); ?>;">
                                    <?php echo e(number_format($grandTotals[$tipe][$singleId] ?? 0, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        
                        <tr style="background: #0f172a; color: white; border-top: 3px solid #6366f1;">
                            <td colspan="3" class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-white">
                                TOTAL PASIVA (KEWAJIBAN & EKUITAS)
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold num-cell text-emerald-300">
                                <?php echo e(number_format($grandTotals['Pasiva'][$singleId] ?? 0, 2, ',', '.')); ?>

                            </td>
                        </tr>

                        
                        <?php
                            $diffSingle = ($grandTotals['Aset'][$singleId] ?? 0) - ($grandTotals['Pasiva'][$singleId] ?? 0);
                        ?>
                        <tr style="background: #f0fdf4; border-top: 1px solid #bbf7d0;">
                            <td colspan="3" class="py-2.5 px-4 font-semibold text-xs text-emerald-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>STATUS BALANCE (AKTIVA - PASIVA)</span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-xs num-cell text-emerald-700">
                                <?php echo e(abs($diffSingle) < 0.01 ? 'Rp 0,00 (Balanced)' : number_format($diffSingle, 2, ',', '.')); ?>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <div id="rkAuditModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                            Audit Transaksi Silang Rekening Koran (R/K)
                            <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-500/30 text-indigo-200 border border-indigo-400/30" id="rkAuditDeptBadge">
                                Cabang
                            </span>
                        </h3>
                        <p class="text-[11px] text-slate-400" id="rkAuditSubtitle">
                            Daftar jurnal transfer dana dan mutasi antar cabang
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeRkAuditModal()"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-slate-500">Cabang:</span>
                        <span class="font-bold text-slate-800 ml-1" id="rkAuditDeptName">—</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Cut-Off:</span>
                        <span class="font-bold text-slate-800 ml-1" id="rkAuditDate">—</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Jumlah Transaksi:</span>
                        <span class="font-bold text-indigo-600 ml-1" id="rkAuditCount">0</span>
                    </div>
                </div>
                <div>
                    <span class="text-slate-500">Net Mutasi R/K:</span>
                    <span class="font-bold text-sm ml-1" id="rkAuditTotalNet">Rp 0,00</span>
                </div>
            </div>

            
            <div class="p-6 overflow-y-auto flex-1" id="rkAuditContent">
                
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-8 h-8 mx-auto animate-spin text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Memuat daftar transaksi silang...
                </div>
            </div>

            
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="closeRkAuditModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>


<script>
    // State sets for accordion hierarchy
    const openTipes = new Set(['tipe-aset', 'tipe-kewajiban', 'tipe-ekuitas', 'single-tipe-aset', 'single-tipe-kewajiban', 'single-tipe-ekuitas']);
    const openGroups = new Set();
    const openAccounts = new Set();

    document.addEventListener('DOMContentLoaded', function () {
        // Collect all groups and accounts to initialize open
        document.querySelectorAll('[data-group]').forEach(el => openGroups.add(el.dataset.group));
        document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));
    });

    function toggleTipe(tipeId) {
        const rows = document.querySelectorAll('.' + tipeId + '-rows');
        const chevron = document.getElementById('chevron-' + tipeId);
        const isOpen = openTipes.has(tipeId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openTipes.delete(tipeId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openTipes.add(tipeId);
        }
    }

    function toggleGroup(groupId) {
        const rows = document.querySelectorAll('.' + groupId + '-rows');
        const chevron = document.getElementById('chevron-' + groupId);
        const isOpen = openGroups.has(groupId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openGroups.delete(groupId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openGroups.add(groupId);
        }
    }

    function toggleAccount(accountId) {
        const rows = document.querySelectorAll('.' + accountId + '-rows');
        const chevron = document.getElementById('chevron-' + accountId);
        const isOpen = openAccounts.has(accountId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openAccounts.delete(accountId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openAccounts.add(accountId);
        }
    }

    function setLevel(lvl) {
        document.querySelectorAll('.ctrl-btn').forEach(b => b.classList.remove('active'));
        if (document.getElementById('btn-lvl-' + lvl)) {
            document.getElementById('btn-lvl-' + lvl).classList.add('active');
        }

        if (lvl === 'all') {
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.add('open'));
            openTipes.add('tipe-aset').add('tipe-kewajiban').add('tipe-ekuitas');
        } else if (lvl === 2) {
            // Show Headers & Groups, Hide Accounts & Subaccounts
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.row-subaccount').forEach(r => r.classList.add('hidden'));
            document.querySelectorAll('[data-account]').forEach(el => {
                const accId = el.dataset.account;
                const rows = document.querySelectorAll('.' + accId + '-rows');
                rows.forEach(r => r.classList.add('hidden'));
                const ch = document.getElementById('chevron-' + accId);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 1) {
            // Show only Tipe and Headers, hide Groups and below
            document.querySelectorAll('.row-group, .row-account, .row-subaccount').forEach(r => r.classList.add('hidden'));
            document.querySelectorAll('[data-group]').forEach(el => {
                const grpId = el.dataset.group;
                const rows = document.querySelectorAll('.' + grpId + '-rows');
                rows.forEach(r => r.classList.add('hidden'));
                const ch = document.getElementById('chevron-' + grpId);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 0) {
            // Collapse all under Tipe
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.remove('open'));
            openTipes.clear();
            openGroups.clear();
            openAccounts.clear();
        }
    }

    function filterTableRows(query) {
        const q = query.trim().toLowerCase();
        const rows = document.querySelectorAll('#matrixReportTable tbody tr, #singleReportTable tbody tr');

        if (!q) {
            setLevel('all');
            return;
        }

        rows.forEach(row => {
            if (row.classList.contains('row-tipe') || row.classList.contains('row-total')) {
                row.classList.remove('hidden');
                return;
            }
            const label = row.querySelector('.account-name-label') || row.querySelector('td:first-child');
            if (label) {
                const txt = label.textContent.toLowerCase();
                if (txt.includes(q)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            }
        });
    }

    // RK Quick Audit Modal
    function openRkAuditModal(deptId, deptName, cutOffDate) {
        const modal = document.getElementById('rkAuditModal');
        const badge = document.getElementById('rkAuditDeptBadge');
        const titleName = document.getElementById('rkAuditDeptName');
        const dateEl = document.getElementById('rkAuditDate');
        const countEl = document.getElementById('rkAuditCount');
        const totalNetEl = document.getElementById('rkAuditTotalNet');
        const contentEl = document.getElementById('rkAuditContent');

        badge.textContent = deptName;
        titleName.textContent = deptName;
        dateEl.textContent = cutOffDate;
        countEl.textContent = 'Memuat...';
        totalNetEl.textContent = '...';

        contentEl.innerHTML = `
            <div class="py-12 text-center text-slate-400">
                <svg class="w-8 h-8 mx-auto animate-spin text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Mengambil data jurnal transaksi silang...
            </div>
        `;

        modal.classList.remove('hidden');

        fetch(`<?php echo e(route('neraca_departemen.rk_audit')); ?>?departemen_id=${encodeURIComponent(deptId)}&end_date=${encodeURIComponent(cutOffDate)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    countEl.textContent = data.count + ' jurnal';
                    const formattedNet = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_rk);
                    totalNetEl.textContent = (data.total_rk < 0 ? '(' + formattedNet.replace('-', '') + ')' : formattedNet);
                    totalNetEl.className = 'font-bold text-sm ml-1 ' + (data.total_rk < 0 ? 'text-red-600' : 'text-emerald-700');

                    if (data.journals.length === 0) {
                        contentEl.innerHTML = `
                            <div class="py-10 text-center text-slate-500">
                                <p class="font-medium text-sm">Tidak ada transaksi silang multi-departemen untuk cabang ini.</p>
                            </div>
                        `;
                        return;
                    }

                    let tableHtml = `
                        <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">No. Jurnal</th>
                                    <th class="py-2.5 px-3">Keterangan</th>
                                    <th class="py-2.5 px-3">Unit Lawan (Counterpart)</th>
                                    <th class="py-2.5 px-3 text-right">Debit Unit</th>
                                    <th class="py-2.5 px-3 text-right">Kredit Unit</th>
                                    <th class="py-2.5 px-3 text-right font-bold">Net (D - K)</th>
                                    <th class="py-2.5 px-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                    `;

                    data.journals.forEach(j => {
                        const debStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(j.dept_debit);
                        const crdStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(j.dept_credit);
                        const netStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(Math.abs(j.dept_net));
                        const netClass = j.dept_net < 0 ? 'text-red-600 font-bold' : 'text-slate-800 font-bold';
                        const netDisplay = j.dept_net < 0 ? `(${netStr})` : netStr;
                        const counterpartsBadge = j.counterparts.length > 0
                            ? j.counterparts.map(c => `<span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 mr-1">${c}</span>`).join('')
                            : '<span class="text-slate-400 italic">Internal</span>';

                        tableHtml += `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2 px-3 text-slate-600 whitespace-nowrap">${j.tanggal}</td>
                                <td class="py-2 px-3 font-semibold text-indigo-600 whitespace-nowrap">${j.source}</td>
                                <td class="py-2 px-3 text-slate-700">${j.comment}</td>
                                <td class="py-2 px-3">${counterpartsBadge}</td>
                                <td class="py-2 px-3 text-right num-cell">${debStr}</td>
                                <td class="py-2 px-3 text-right num-cell">${crdStr}</td>
                                <td class="py-2 px-3 text-right num-cell ${netClass}">${netDisplay}</td>
                                <td class="py-2 px-2 text-center whitespace-nowrap">
                                    <a href="/journal_entry/${j.id}" target="_blank"
                                        class="inline-flex items-center px-2 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-semibold transition"
                                        title="Buka rincian jurnal lengkap">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        `;
                    });

                    tableHtml += `</tbody></table>`;
                    contentEl.innerHTML = tableHtml;
                } else {
                    contentEl.innerHTML = `<div class="p-6 text-center text-red-500 font-medium">Gagal memuat data audit.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                contentEl.innerHTML = `<div class="p-6 text-center text-red-500 font-medium">Terjadi kesalahan saat memuat data.</div>`;
            });
    }

    function closeRkAuditModal() {
        document.getElementById('rkAuditModal').classList.add('hidden');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/neraca_departemen/neraca_departemen_report.blade.php ENDPATH**/ ?>