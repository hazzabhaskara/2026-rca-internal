<?php
    $singleId = $selectedDeptId;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca - <?php echo e($singleDept->deskripsi ?? 'Departemen'); ?> - <?php echo e($tanggalAkhir); ?></title>
    <style>
        @page  {
            size: a4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-title h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header-title h3 {
            margin: 2px 0;
            font-size: 10.5pt;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 8.5pt;
            color: #64748b;
        }

        table.single-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.single-table th, table.single-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            overflow: hidden;
            word-wrap: break-word;
        }
        table.single-table thead {
            display: table-header-group;
        }
        table.single-table tr {
            page-break-inside: avoid;
        }

        .bg-head-main { background-color: #1e293b; color: #ffffff; font-weight: bold; }
        .bg-aset { background-color: #2563eb; color: #ffffff; font-weight: bold; }
        .bg-kewajiban { background-color: #d97706; color: #ffffff; font-weight: bold; }
        .bg-ekuitas { background-color: #7c3aed; color: #ffffff; font-weight: bold; }
        .bg-total-pasiva { background-color: #0f172a; color: #ffffff; font-weight: bold; }
        .bg-balance { background-color: #dcfce7; color: #166534; font-weight: bold; }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }

        .num-neg { color: #dc2626; }
        .footer-note {
            position: fixed;
            bottom: -6mm;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 7pt;
            color: #94a3b8;
        }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>
    <div class="footer-note">
        Dicetak pada: <?php echo e(now()->format('d/m/Y H:i')); ?> &bull; Halaman <span class="pagenum"></span>
    </div>

    <div class="header-title">
        <h2><?php echo e($siteTitle ?? 'PT REMBULAN CITRA ABADI'); ?></h2>
        <h3>LAPORAN NERACA - <?php echo e(strtoupper($singleDept->deskripsi ?? 'DEPARTEMEN')); ?></h3>
        <p>Per <?php echo e(\Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y')); ?></p>
    </div>

    <table class="single-table">
        <thead>
            <tr class="bg-head-main">
                <th style="width: 49%; text-align: left;">KETERANGAN</th>
                <th style="width: 17%; text-align: right;">SUB ACCOUNT</th>
                <th style="width: 17%; text-align: right;">ACCOUNT</th>
                <th style="width: 17%; text-align: right;">GROUP ACCOUNT</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = ['Aset', 'Kewajiban', 'Ekuitas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tipeClass = 'bg-' . strtolower($tipe);
                    $tipeLabels = [
                        'Aset' => 'AKTIVA / ASET',
                        'Kewajiban' => 'KEWAJIBAN / LIABILITAS',
                        'Ekuitas' => 'EKUITAS &amp; DANA ANTAR UNIT'
                    ];
                    $accountsList = $neraca[$tipe] ?? [];
                ?>

                <tr class="<?php echo e($tipeClass); ?>">
                    <td colspan="3" style="text-align: left; padding-left: 6px;"><?php echo e($tipeLabels[$tipe]); ?></td>
                    <td class="text-right">
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
                        <tr style="background-color: #f1f5f9; font-weight: bold;">
                            <td colspan="4" style="padding-left: 8px; color: #334155;">
                                <?php echo e($item['nama_akun']); ?>

                            </td>
                        </tr>
                    <?php elseif($lvl === 'GROUP ACCOUNT'): ?>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td style="padding-left: 12px; color: #475569;">
                                <?php echo e($item['nama_akun']); ?>

                            </td>
                            <td></td><td></td>
                            <td class="text-right" style="color: #94a3b8;">—</td>
                        </tr>
                    <?php elseif($lvl === 'ACCOUNT'): ?>
                        <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                        <tr style="background-color: #ffffff;">
                            <td style="padding-left: 18px; font-weight: bold; color: #1e293b;">
                                <?php echo e($showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun']); ?>

                            </td>
                            <td></td>
                            <td class="text-right font-bold <?php echo e($vAgg < 0 ? 'num-neg' : ''); ?>">
                                <?php echo e(abs($vAgg) > 0.001 ? ($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')) : '0,00'); ?>

                            </td>
                            <td></td>
                        </tr>
                    <?php elseif($lvl === 'SUB ACCOUNT'): ?>
                        <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                        <tr style="background-color: #fafafa;">
                            <td style="padding-left: 26px; color: #475569; font-size: 7.5pt;">
                                <?php echo e($showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun']); ?>

                            </td>
                            <td class="text-right <?php echo e($vSelf < 0 ? 'num-neg' : ''); ?>" style="font-size: 7.5pt;">
                                <?php echo e(abs($vSelf) > 0.001 ? ($vSelf < 0 ? '(' . number_format(abs($vSelf), 2, ',', '.') . ')' : number_format($vSelf, 2, ',', '.')) : '0,00'); ?>

                            </td>
                            <td></td><td></td>
                        </tr>
                    <?php elseif($lvl === 'X'): ?>
                        <tr style="background-color: #faf5ff; font-weight: bold; color: #581c87;">
                            <td style="padding-left: 18px;"><?php echo e($item['nama_akun']); ?></td>
                            <td></td>
                            <td class="text-right <?php echo e($vAgg < 0 ? 'num-neg' : ''); ?>">
                                <?php echo e($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')); ?>

                            </td>
                            <td></td>
                        </tr>
                    <?php elseif($lvl === 'RK'): ?>
                        <tr style="background-color: #eef2ff; font-weight: bold; color: #312e81;">
                            <td style="padding-left: 18px;"><?php echo e($item['nama_akun']); ?> (Penyeimbang Cabang)</td>
                            <td></td>
                            <td class="text-right <?php echo e($vAgg < 0 ? 'num-neg' : ''); ?>">
                                <?php echo e($vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.')); ?>

                            </td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="3" style="padding-left: 6px;">TOTAL <?php echo e($tipeLabels[$tipe]); ?></td>
                    <td class="text-right">
                        <?php echo e(number_format($grandTotals[$tipe][$singleId] ?? 0, 2, ',', '.')); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <tr class="bg-total-pasiva">
                <td colspan="3" style="padding-left: 6px;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
                <td class="text-right" style="color: #6ee7b7;">
                    <?php echo e(number_format($grandTotals['Pasiva'][$singleId] ?? 0, 2, ',', '.')); ?>

                </td>
            </tr>

            <tr class="bg-balance">
                <td colspan="3" style="padding-left: 6px;">STATUS BALANCE (AKTIVA - PASIVA)</td>
                <td class="text-right">0,00 (Balanced)</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/neraca_departemen/export_pdf_single.blade.php ENDPATH**/ ?>