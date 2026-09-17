<?php
    $singleId = $selectedDeptId;
?>
<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 14pt; font-weight: bold; text-align: center;">
                <?php echo e($siteTitle ?? 'PT REMBULAN CITRA ABADI'); ?>

            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN NERACA - <?php echo e(strtoupper($singleDept->deskripsi ?? 'DEPARTEMEN')); ?>

            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 10pt; text-align: center;">
                Per <?php echo e(\Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y')); ?>

            </th>
        </tr>
        <tr><th colspan="4"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 45; text-align: left;">KETERANGAN</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">SUB ACCOUNT</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">ACCOUNT</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">GROUP ACCOUNT</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = ['Aset', 'Kewajiban', 'Ekuitas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $tipeNames = [
                    'Aset' => 'AKTIVA / ASET',
                    'Kewajiban' => 'KEWAJIBAN / LIABILITAS',
                    'Ekuitas' => 'EKUITAS &amp; DANA ANTAR UNIT'
                ];
                $accountsList = $neraca[$tipe] ?? [];
            ?>
            <tr style="background-color: #3b82f6; color: #ffffff; font-weight: bold;">
                <td colspan="3" style="border: 1px solid #000000; font-weight: bold;"><?php echo e($tipeNames[$tipe]); ?></td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe][$singleId] ?? 0); ?>

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
                        <td colspan="4" style="border: 1px solid #cccccc; font-weight: bold;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                    </tr>
                <?php elseif($lvl === 'GROUP ACCOUNT'): ?>
                    <tr style="background-color: #f8fafc; font-weight: bold;">
                        <td style="border: 1px solid #cccccc; font-weight: bold; padding-left: 10px;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                        <td style="border: 1px solid #cccccc;"></td>
                        <td style="border: 1px solid #cccccc;"></td>
                        <td style="border: 1px solid #cccccc; text-align: right;"></td>
                    </tr>
                <?php elseif($lvl === 'ACCOUNT'): ?>
                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                    <tr style="background-color: #ffffff;">
                        <td style="border: 1px solid #e2e8f0; font-weight: 600; padding-left: 20px;">
                            <?php echo e($showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun']); ?>

                        </td>
                        <td style="border: 1px solid #e2e8f0;"></td>
                        <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: 600;">
                            <?php echo e($vAgg); ?>

                        </td>
                        <td style="border: 1px solid #e2e8f0;"></td>
                    </tr>
                <?php elseif($lvl === 'SUB ACCOUNT'): ?>
                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                    <tr style="background-color: #fafafa;">
                        <td style="border: 1px solid #f1f5f9; padding-left: 30px; font-style: italic;">
                            <?php echo e($showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun']); ?>

                        </td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            <?php echo e($vSelf); ?>

                        </td>
                        <td style="border: 1px solid #f1f5f9;"></td>
                        <td style="border: 1px solid #f1f5f9;"></td>
                    </tr>
                <?php elseif($lvl === 'X'): ?>
                    <tr style="background-color: #faf5ff; font-weight: bold;">
                        <td style="border: 1px solid #d8b4fe; font-weight: bold; padding-left: 20px;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                        <td style="border: 1px solid #d8b4fe;"></td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            <?php echo e($vAgg); ?>

                        </td>
                        <td style="border: 1px solid #d8b4fe;"></td>
                    </tr>
                <?php elseif($lvl === 'RK'): ?>
                    <tr style="background-color: #eef2ff; font-weight: bold;">
                        <td style="border: 1px solid #c7d2fe; font-weight: bold; padding-left: 20px;">
                            <?php echo e($item['nama_akun']); ?> (Penyeimbang Cabang)
                        </td>
                        <td style="border: 1px solid #c7d2fe;"></td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            <?php echo e($vAgg); ?>

                        </td>
                        <td style="border: 1px solid #c7d2fe;"></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="3" style="border: 1px solid #000000; font-weight: bold;">TOTAL <?php echo e($tipeNames[$tipe]); ?></td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe][$singleId] ?? 0); ?>

                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="3" style="border: 1px solid #000000; font-weight: bold;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                <?php echo e($grandTotals['Pasiva'][$singleId] ?? 0); ?>

            </td>
        </tr>

        <tr style="background-color: #dcfce7; font-weight: bold;">
            <td colspan="3" style="border: 1px solid #86efac; font-weight: bold;">STATUS BALANCE (AKTIVA - PASIVA)</td>
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
        </tr>
    </tbody>
</table>
<?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/neraca_departemen/export_excel_single.blade.php ENDPATH**/ ?>