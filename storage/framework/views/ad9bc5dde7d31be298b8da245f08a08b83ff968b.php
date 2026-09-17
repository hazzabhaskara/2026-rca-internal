<table>
    <thead>
        <tr>
            <th colspan="<?php echo e(count($departments) + 4); ?>" style="font-size: 14pt; font-weight: bold; text-align: center;">
                <?php echo e($siteTitle ?? 'PT REMBULAN CITRA ABADI'); ?>

            </th>
        </tr>
        <tr>
            <th colspan="<?php echo e(count($departments) + 4); ?>" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN NERACA DEPARTEMEN (MATRIKS KOMPARATIF)
            </th>
        </tr>
        <tr>
            <th colspan="<?php echo e(count($departments) + 4); ?>" style="font-size: 10pt; text-align: center;">
                Per <?php echo e(\Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y')); ?>

            </th>
        </tr>
        <tr><th colspan="<?php echo e(count($departments) + 4); ?>"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 35; text-align: left;">KETERANGAN / NAMA AKUN</th>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="border: 1px solid #000000; width: 18; text-align: right;">
                    <?php echo e($dept->kode); ?> - <?php echo e($dept->deskripsi); ?>

                </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">NON-DEPARTEMEN</th>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">ELIMINASI R/K</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">TOTAL KONSOLIDASI</th>
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
                <td style="border: 1px solid #000000; font-weight: bold;"><?php echo e($tipeNames[$tipe]); ?></td>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        <?php echo e($grandTotals[$tipe][$dept->id] ?? 0); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['unassigned'] ?? 0); ?>

                </td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['eliminasi'] ?? 0); ?>

                </td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['total'] ?? 0); ?>

                </td>
            </tr>

            <?php $__currentLoopData = $accountsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $lvl = $item['level_akun'];
                    $isZero = $hideAccountWithZero && collect($item['balances'])->every(fn($v) => abs($v) < 0.001);
                ?>

                <?php if($lvl === 'HEADER'): ?>
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="<?php echo e(count($departments) + 4); ?>" style="border: 1px solid #cccccc; font-weight: bold;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                    </tr>
                <?php elseif($lvl === 'GROUP ACCOUNT'): ?>
                    <tr style="background-color: #f8fafc; font-weight: bold;">
                        <td style="border: 1px solid #cccccc; font-weight: bold; padding-left: 10px;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="border: 1px solid #cccccc; text-align: right;"></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #cccccc; text-align: right;"></td>
                        <td style="border: 1px solid #cccccc; text-align: right;"></td>
                        <td style="border: 1px solid #cccccc; text-align: right;"></td>
                    </tr>
                <?php elseif($lvl === 'ACCOUNT'): ?>
                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                    <tr style="background-color: #ffffff;">
                        <td style="border: 1px solid #e2e8f0; font-weight: 600; padding-left: 20px;">
                            <?php echo e($showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun']); ?>

                        </td>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="border: 1px solid #e2e8f0; text-align: right;">
                                <?php echo e($item['balances'][$dept->id] ?? 0); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #e2e8f0; text-align: right;">
                            <?php echo e($item['balances']['unassigned'] ?? 0); ?>

                        </td>
                        <td style="border: 1px solid #e2e8f0; text-align: right;">0</td>
                        <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: bold;">
                            <?php echo e($item['balances']['total'] ?? 0); ?>

                        </td>
                    </tr>
                <?php elseif($lvl === 'SUB ACCOUNT'): ?>
                    <?php if($isZero): ?> <?php continue; ?> <?php endif; ?>
                    <tr style="background-color: #fafafa;">
                        <td style="border: 1px solid #f1f5f9; padding-left: 30px; font-style: italic;">
                            <?php echo e($showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun']); ?>

                        </td>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="border: 1px solid #f1f5f9; text-align: right;">
                                <?php echo e($item['balances'][$dept->id] ?? 0); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            <?php echo e($item['balances']['unassigned'] ?? 0); ?>

                        </td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">0</td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            <?php echo e($item['balances']['total'] ?? 0); ?>

                        </td>
                    </tr>
                <?php elseif($lvl === 'X'): ?>
                    <tr style="background-color: #faf5ff; font-weight: bold;">
                        <td style="border: 1px solid #d8b4fe; font-weight: bold; padding-left: 20px;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                                <?php echo e($item['balances'][$dept->id] ?? 0); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            <?php echo e($item['balances']['unassigned'] ?? 0); ?>

                        </td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">0</td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            <?php echo e($item['balances']['total'] ?? 0); ?>

                        </td>
                    </tr>
                <?php elseif($lvl === 'RK'): ?>
                    <tr style="background-color: #eef2ff; font-weight: bold;">
                        <td style="border: 1px solid #c7d2fe; font-weight: bold; padding-left: 20px;">
                            <?php echo e($item['nama_akun']); ?>

                        </td>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                                <?php echo e($item['balances'][$dept->id] ?? 0); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            <?php echo e($item['balances']['unassigned'] ?? 0); ?>

                        </td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            <?php echo e($item['balances']['eliminasi'] ?? 0); ?>

                        </td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">0</td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td style="border: 1px solid #000000; font-weight: bold;">TOTAL <?php echo e($tipeNames[$tipe]); ?></td>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        <?php echo e($grandTotals[$tipe][$dept->id] ?? 0); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['unassigned'] ?? 0); ?>

                </td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['eliminasi'] ?? 0); ?>

                </td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals[$tipe]['total'] ?? 0); ?>

                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td style="border: 1px solid #000000; font-weight: bold;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    <?php echo e($grandTotals['Pasiva'][$dept->id] ?? 0); ?>

                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                <?php echo e($grandTotals['Pasiva']['unassigned'] ?? 0); ?>

            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                <?php echo e($grandTotals['Pasiva']['eliminasi'] ?? 0); ?>

            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                <?php echo e($grandTotals['Pasiva']['total'] ?? 0); ?>

            </td>
        </tr>

        
        <tr style="background-color: #dcfce7; font-weight: bold;">
            <td style="border: 1px solid #86efac; font-weight: bold;">STATUS BALANCE (AKTIVA - PASIVA)</td>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
        </tr>
    </tbody>
</table>
<?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/neraca_departemen/export_excel_matrix.blade.php ENDPATH**/ ?>