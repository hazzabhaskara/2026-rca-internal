<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Tests\TestCase;

class BukuBesarDynamicNavigationTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * Helper to get a valid journal entry with details.
     */
    protected function getTestJournalEntry()
    {
        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry, "A journal entry with details must exist in the database for testing.");
        return $entry;
    }

    // =========================================================================
    // R1: DYNAMIC RETURN NAVIGATION TO BUKU BESAR
    // =========================================================================

    /**
     * T1.1: Buku Besar report table links include back_url with all active filter parameters.
     */
    public function testBukuBesarTableLinksIncludeBackUrlWithActiveFilters()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail);
        $entry = $detail->journalEntry;

        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $detail->kode_akun,
        ]);

        $response = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $response->assertStatus(200);

        $content = $response->getContent();

        // Check for edit link with back_url query parameter
        preg_match('/journal_entry\/' . $entry->id . '\/edit\?back_url=([^"\'\s>&]+)/', $content, $matches);
        $this->assertNotEmpty($matches, "Buku Besar table Source column must link to journal_entry.edit with back_url query parameter");

        $decodedBackUrl = urldecode($matches[1]);
        $this->assertStringContainsString('/laporan/buku-besar', $decodedBackUrl);
        $this->assertStringContainsString('start_date=' . $entry->tanggal, $decodedBackUrl);
        $this->assertStringContainsString('end_date=' . $entry->tanggal, $decodedBackUrl);
    }

    /**
     * T1.2: Journal entry update redirects to back_url with SweetAlert success flash.
     */
    public function testJournalEntryUpdateRedirectsBackToBukuBesarWithSweetAlertFlash()
    {
        $entry = $this->getTestJournalEntry();
        $targetBackUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'departemen_id' => 'all',
        ]);

        $details = $entry->details;
        $items = [];
        foreach ($details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test updated comment ' . time(),
            'items' => $items,
            'back_url' => $targetBackUrl,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);

        // Assert 302 Redirect to exact target back_url
        $response->assertStatus(302);
        $response->assertRedirect($targetBackUrl);

        // Assert session success message
        $response->assertSessionHas('success', 'Journal entry berhasil diperbarui.');
    }

    /**
     * T1.3: Data refresh - updated journal data is immediately reflected in Buku Besar query.
     */
    public function testBukuBesarDataRefreshesImmediatelyAfterUpdate()
    {
        $entry = $this->getTestJournalEntry();
        $uniqueComment = 'RefreshTest_' . uniqid();

        $details = $entry->details;
        $firstDetail = $details->first();
        $items = [];
        foreach ($details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $targetBackUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $firstDetail->kode_akun,
            'show_comment' => 'transaction_comment',
        ]);

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => $uniqueComment,
            'items' => $items,
            'back_url' => $targetBackUrl,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertRedirect($targetBackUrl);

        // Now fetch Buku Besar with the exact targetBackUrl
        $bukuBesarResponse = $this->actingAs($this->adminUser)->get($targetBackUrl);
        $bukuBesarResponse->assertStatus(200);
        $bukuBesarResponse->assertSee($uniqueComment);
    }

    // =========================================================================
    // R2: CHAINED NAVIGATION THROUGH JOURNAL DETAIL (SHOW)
    // =========================================================================

    /**
     * T2.1: Buku Besar links on Debet & Kredit lead to journal_entry.show with back_url.
     */
    public function testBukuBesarDebetKreditLinksContainShowRouteAndBackUrl()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail);
        $entry = $detail->journalEntry;

        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'selected_accounts' => $detail->kode_akun,
        ]);

        $response = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $response->assertStatus(200);

        $content = $response->getContent();

        preg_match('/journal_entry\/' . $entry->id . '\?back_url=([^"\'\s>&]+)/', $content, $matches);
        $this->assertNotEmpty($matches, "Debet and Kredit columns in Buku Besar must link to journal_entry.show with back_url parameter");

        $decodedBackUrl = urldecode($matches[1]);
        $this->assertStringContainsString('/laporan/buku-besar', $decodedBackUrl);
        $this->assertStringContainsString('start_date=' . $entry->tanggal, $decodedBackUrl);
        $this->assertStringContainsString('end_date=' . $entry->tanggal, $decodedBackUrl);
    }

    /**
     * T2.2: journal_entry.show "Kembali" button points back to Buku Besar URL.
     */
    public function testShowPageKembaliButtonReturnsToBukuBesar()
    {
        $entry = $this->getTestJournalEntry();
        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
            'departemen_id' => '1',
        ]);

        $showUrl = route('journal_entry.show', $entry->id) . '?back_url=' . urlencode($bukuBesarUrl);
        $response = $this->actingAs($this->adminUser)->get($showUrl);
        $response->assertStatus(200);

        $content = $response->getContent();
        // Check "Kembali" button href (HTML encoded)
        $this->assertStringContainsString('href="' . e($bukuBesarUrl) . '"', $content,
            "Tombol 'Kembali' on show page must point to the provided back_url");
    }

    /**
     * T2.3: journal_entry.show "Edit" button forwards back_url to journal_entry.edit.
     */
    public function testShowPageEditButtonChainsBackUrlToEditForm()
    {
        $entry = $this->getTestJournalEntry();
        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
            'departemen_id' => '1',
        ]);

        $showUrl = route('journal_entry.show', $entry->id) . '?back_url=' . urlencode($bukuBesarUrl);
        $response = $this->actingAs($this->adminUser)->get($showUrl);
        $response->assertStatus(200);

        $content = $response->getContent();
        $expectedEditWithChain = route('journal_entry.edit', $entry->id) . '?back_url=' . urlencode($bukuBesarUrl);

        $this->assertStringContainsString('href="' . e($expectedEditWithChain) . '"', $content,
            "Tombol 'Edit' on show page must forward the back_url parameter to the edit form");
    }

    // =========================================================================
    // R3: FORM STATE & CANCEL BUTTON PRESERVATION
    // =========================================================================

    /**
     * T3.1: Edit page preserves back_url in hidden input and Cancel button.
     */
    public function testEditPagePreservesBackUrlInHiddenInputAndCancelButton()
    {
        $entry = $this->getTestJournalEntry();
        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-31',
            'departemen_id' => 'none',
        ]);

        $editUrl = route('journal_entry.edit', $entry->id) . '?back_url=' . urlencode($bukuBesarUrl);
        $response = $this->actingAs($this->adminUser)->get($editUrl);
        $response->assertStatus(200);

        $content = $response->getContent();

        // Check hidden input
        $this->assertStringContainsString('name="back_url" value="' . e($bukuBesarUrl) . '"', $content,
            "Edit form must include hidden back_url input");

        // Check Cancel button
        $this->assertStringContainsString('href="' . e($bukuBesarUrl) . '"', $content,
            "Cancel button on edit page must link to back_url");
    }

    /**
     * T3.2: Validation error preserves back_url via old('back_url').
     */
    public function testValidationErrorPreservesBackUrlViaOldInput()
    {
        $entry = $this->getTestJournalEntry();
        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-04-01',
            'end_date' => '2025-04-30',
        ]);

        // Submit invalid unbalanced items (Debit 1000, Credit 500)
        $invalidPayload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Unbalanced journal test',
            'back_url' => $bukuBesarUrl,
            'items' => [
                [
                    'kode_akun' => $entry->details->first()->kode_akun,
                    'departemen_akun_id' => null,
                    'debits' => '1000',
                    'credits' => '0',
                    'comment' => 'Debit side',
                ],
                [
                    'kode_akun' => $entry->details->last()->kode_akun,
                    'departemen_akun_id' => null,
                    'debits' => '0',
                    'credits' => '500', // Unbalanced!
                    'comment' => 'Credit side',
                ]
            ],
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $invalidPayload);

        // Validation fails with redirect back and errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('items');

        // Follow redirect back to edit form with session flash & old input
        $editResponse = $this->actingAs($this->adminUser)
            ->withSession([
                '_old_input' => $invalidPayload,
            ])
            ->get(route('journal_entry.edit', $entry->id));

        $editResponse->assertStatus(200);
        $content = $editResponse->getContent();

        // Must still have back_url in hidden input and Cancel button via old('back_url')
        $this->assertStringContainsString('name="back_url" value="' . e($bukuBesarUrl) . '"', $content,
            "Hidden input must preserve back_url after validation failure using old('back_url')");
        $this->assertStringContainsString('href="' . e($bukuBesarUrl) . '"', $content,
            "Cancel button must preserve back_url after validation failure");
    }

    // =========================================================================
    // R4: SECURITY & ZERO-REGRESSION GUARDRAILS
    // =========================================================================

    /**
     * T4.1: Open redirect attempt with external URL is safely blocked by controller.
     */
    public function testOpenRedirectRejectedByController()
    {
        $entry = $this->getTestJournalEntry();
        $details = $entry->details;
        $items = [];
        foreach ($details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $maliciousUrls = [
            'https://malicious-site.com/steal-token',
            'http://attacker.com',
            '//evil.example.com',
            url('/') . '.attacker.com/bypass',
        ];

        foreach ($maliciousUrls as $evilUrl) {
            $payload = [
                'source' => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Security test',
                'items' => $items,
                'back_url' => $evilUrl,
            ];

            $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);

            $response->assertStatus(302);
            // Must redirect to standard journal_entry.index, NOT to the evil URL
            $response->assertRedirect(route('journal_entry.index'));
        }
    }

    /**
     * T4.2: Open redirect attempt in edit view sanitizes Cancel and hidden back_url to index.
     */
    public function testOpenRedirectSanitizedInEditView()
    {
        $entry = $this->getTestJournalEntry();
        $evilUrl = 'https://malicious-site.com/phishing';

        $response = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.edit', $entry->id) . '?back_url=' . urlencode($evilUrl));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Must NOT contain evil URL
        $this->assertStringNotContainsString($evilUrl, $content,
            "Edit page must not output external/malicious URLs in Cancel button or hidden input");

        // Must fallback to journal_entry.index
        $this->assertStringContainsString('href="' . route('journal_entry.index') . '"', $content);
        $this->assertStringContainsString('value="' . route('journal_entry.index') . '"', $content);
    }

    /**
     * T4.3: Open redirect attempt in show view sanitizes Kembali and Edit links.
     */
    public function testOpenRedirectSanitizedInShowView()
    {
        $entry = $this->getTestJournalEntry();
        $evilUrl = 'https://malicious-site.com/phishing';

        $response = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.show', $entry->id) . '?back_url=' . urlencode($evilUrl));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Must NOT contain evil URL
        $this->assertStringNotContainsString($evilUrl, $content,
            "Show page must not output external/malicious URLs in Kembali button");

        // Must fallback to journal_entry.index
        $this->assertStringContainsString('href="' . route('journal_entry.index') . '"', $content);
        $this->assertStringNotContainsString('back_url=' . urlencode($evilUrl), $content);
    }

    /**
     * T4.4: Backward compatibility: editing from standard list without back_url falls back to index.
     */
    public function testBackwardCompatibilityWithoutBackUrl()
    {
        $entry = $this->getTestJournalEntry();
        $details = $entry->details;
        $items = [];
        foreach ($details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        // 1. Submit update without back_url
        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Backward compat test',
            'items' => $items,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertStatus(302);
        $response->assertRedirect(route('journal_entry.index'));
        $response->assertSessionHas('success', 'Journal entry berhasil diperbarui.');

        // 2. View edit without back_url
        $editResponse = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $editResponse->assertStatus(200);
        $content = $editResponse->getContent();
        $this->assertStringContainsString('href="' . route('journal_entry.index') . '"', $content);

        // 3. View show without back_url
        $showResponse = $this->actingAs($this->adminUser)->get(route('journal_entry.show', $entry->id));
        $showResponse->assertStatus(200);
        $showContent = $showResponse->getContent();
        $this->assertStringContainsString('href="' . route('journal_entry.index') . '"', $showContent);
    }

    /**
     * T4.5: SweetAlert modal session display is properly rendered in layout.
     */
    public function testSweetAlertSessionRendersInLayout()
    {
        $targetUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['success' => 'Journal entry berhasil diperbarui.'])
            ->get($targetUrl);

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Swal.fire', $content);
        $this->assertStringContainsString('Berhasil!', $content);
        $this->assertStringContainsString('Journal entry berhasil diperbarui.', $content);
    }

    /**
     * T4.6: Open redirect attempt on DELETE /journal_entry/{id} is safely blocked.
     */
    public function testOpenRedirectRejectedByDestroy()
    {
        // Create a temporary journal entry to test delete safely
        $entry = $this->getTestJournalEntry();
        $tempEntry = JournalEntry::create([
            'source' => 'TEMP_DEL_TEST',
            'tanggal' => $entry->tanggal,
            'comment' => 'Temporary delete test',
        ]);
        $detail = $entry->details->first();
        JournalEntryDetail::create([
            'journal_entry_id' => $tempEntry->id,
            'kode_akun' => $detail->kode_akun,
            'debits' => 100,
            'credits' => 0,
        ]);
        JournalEntryDetail::create([
            'journal_entry_id' => $tempEntry->id,
            'kode_akun' => $detail->kode_akun,
            'debits' => 0,
            'credits' => 100,
        ]);

        $maliciousUrls = [
            'https://malicious-site.com/steal-token',
            'http://attacker.com',
            url('/') . '.attacker.com/bypass',
        ];

        foreach ($maliciousUrls as $evilUrl) {
            $response = $this->actingAs($this->adminUser)
                ->delete(route('journal_entry.destroy', $tempEntry->id), [
                    'back_url' => $evilUrl,
                ]);

            $response->assertStatus(302);
            $response->assertRedirect(route('journal_entry.index'));
            break; // Entry is deleted on first pass
        }
    }

    /**
     * T4.7: Journal entry index View action button includes back_url parameter.
     */
    public function testJournalEntryIndexViewButtonIncludesBackUrl()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.index', ['search' => 'TEST', 'filter_tahun' => '2025']));

        $response->assertStatus(200);
        $content = $response->getContent();

        preg_match('/journal_entry\/\d+\?back_url=([^"\'\s>&]+)/', $content, $matches);
        $this->assertNotEmpty($matches, "View button in journal_entry.index must include back_url with active query parameters");

        $decodedBackUrl = urldecode($matches[1]);
        $this->assertStringContainsString('journal_entry', $decodedBackUrl);
        $this->assertStringContainsString('search=TEST', $decodedBackUrl);
        $this->assertStringContainsString('filter_tahun=2025', $decodedBackUrl);
    }

    /**
     * T4.8: Full end-to-end chained navigation: Buku Besar -> Show -> Edit -> Update -> Buku Besar.
     */
    public function testFullChainedNavigationFromShowToEditToUpdateRedirectsToBukuBesar()
    {
        $entry = $this->getTestJournalEntry();
        $detail = $entry->details->first();
        $targetBukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $detail->kode_akun,
        ]);

        // Step 1: Open Buku Besar and find the Show link
        $bbResponse = $this->actingAs($this->adminUser)->get($targetBukuBesarUrl);
        $bbResponse->assertStatus(200);
        preg_match('/journal_entry\/' . $entry->id . '\?back_url=([^"\'\s>&]+)/', $bbResponse->getContent(), $showMatches);
        $this->assertNotEmpty($showMatches);

        // Step 2: Navigate to Show using the extracted link
        $showUrl = route('journal_entry.show', $entry->id) . '?back_url=' . $showMatches[1];
        $showResponse = $this->actingAs($this->adminUser)->get($showUrl);
        $showResponse->assertStatus(200);

        // Extract Edit link from Show page
        preg_match('/journal_entry\/' . $entry->id . '\/edit\?back_url=([^"\'\s>&]+)/', $showResponse->getContent(), $editMatches);
        $this->assertNotEmpty($editMatches, "Show page must output an Edit link carrying the back_url");

        // Step 3: Navigate to Edit using the extracted chained link
        $editUrl = route('journal_entry.edit', $entry->id) . '?back_url=' . $editMatches[1];
        $editResponse = $this->actingAs($this->adminUser)->get($editUrl);
        $editResponse->assertStatus(200);

        // Extract hidden back_url input from Edit form
        preg_match('/<input[^>]+name="back_url"[^>]+value="([^"]+)"/', $editResponse->getContent(), $inputMatches);
        $this->assertNotEmpty($inputMatches, "Edit page must include hidden back_url input");
        $safeBackUrlFromForm = html_entity_decode($inputMatches[1]);
        $expectedBackUrl = urldecode($showMatches[1]);
        $this->assertEquals($expectedBackUrl, $safeBackUrlFromForm);

        // Verify that query parameters match target Buku Besar parameters
        parse_str(parse_url($targetBukuBesarUrl, PHP_URL_QUERY), $expectedParams);
        parse_str(parse_url($safeBackUrlFromForm, PHP_URL_QUERY), $actualParams);
        $this->assertEquals($expectedParams, $actualParams);

        // Step 4: Submit Process Update
        $uniqueComment = 'E2E_Chained_' . uniqid();
        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $uniqueComment,
            ];
        }

        $updatePayload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => $uniqueComment,
            'items' => $items,
            'back_url' => $safeBackUrlFromForm,
        ];

        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('journal_entry.update', $entry->id), $updatePayload);

        // Step 5: Assert final redirect lands exactly on target Buku Besar URL with success message
        $updateResponse->assertStatus(302);
        $updateResponse->assertRedirect($safeBackUrlFromForm);
        $updateResponse->assertSessionHas('success', 'Journal entry berhasil diperbarui.');

        // Step 6: Fetch target Buku Besar and confirm data is updated
        $finalBbResponse = $this->actingAs($this->adminUser)->get($safeBackUrlFromForm);
        $finalBbResponse->assertStatus(200);
        $finalBbResponse->assertSee($uniqueComment);
    }

    /**
     * T4.9: Buku Besar report back button sanitizes back_url against open redirect and XSS.
     */
    public function testBukuBesarReportBackButtonOpenRedirectSanitized()
    {
        $maliciousUrls = [
            'https://malicious-site.com/steal-token',
            'http://attacker.com/exploit',
            'javascript:alert(document.cookie)',
            '//evil.example.com/phishing',
            '/\\evil.example.com',
            url('/') . '.attacker.com/bypass',
        ];

        foreach ($maliciousUrls as $evilUrl) {
            $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date' => '2025-01-01',
                'end_date' => '2025-01-31',
                'selected_accounts' => '1101001',
                'back_url' => $evilUrl,
            ]));

            $response->assertStatus(200);
            $content = $response->getContent();

            // Must NOT render evil URL inside any href attribute
            $this->assertStringNotContainsString('href="' . e($evilUrl) . '"', $content,
                "Buku Besar report must not render malicious back_url in links");
            $this->assertStringNotContainsString('href="' . $evilUrl . '"', $content,
                "Buku Besar report must not render unescaped malicious back_url");
        }

        // Valid internal absolute URL must render
        $validInternalUrl = route('buku_besar.filter_buku_besar');
        $validResponse = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'selected_accounts' => '1101001',
            'back_url' => $validInternalUrl,
        ]));
        $validResponse->assertStatus(200);
        $this->assertStringContainsString('href="' . e($validInternalUrl) . '"', $validResponse->getContent());
        $this->assertStringContainsString('Kembali ke Laporan Laba Rugi', $validResponse->getContent());

        // Valid relative internal URL must also render
        $validRelativeUrl = '/filter_buku_besar';
        $validRelativeResponse = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'selected_accounts' => '1101001',
            'back_url' => $validRelativeUrl,
        ]));
        $validRelativeResponse->assertStatus(200);
        $this->assertStringContainsString('href="' . url($validRelativeUrl) . '"', $validRelativeResponse->getContent());
    }

    /**
     * T4.10: Safe relative internal URLs are supported across controller and views.
     */
    public function testRelativeInternalBackUrlSupportedInControllerAndViews()
    {
        $entry = $this->getTestJournalEntry();
        $relativeUrl = '/laporan/buku-besar?start_date=2025-05-01&end_date=2025-05-31&departemen_id=all';
        $expectedAbsoluteUrl = url($relativeUrl);

        // 1. Controller update supports relative back_url and redirects to absolute URL
        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Relative URL test',
            'items' => $items,
            'back_url' => $relativeUrl,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertStatus(302);
        $response->assertRedirect($expectedAbsoluteUrl);

        // 2. Edit page resolves relative URL safely
        $editResponse = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.edit', $entry->id) . '?back_url=' . urlencode($relativeUrl));
        $editResponse->assertStatus(200);
        $editContent = $editResponse->getContent();
        $this->assertStringContainsString('href="' . e($expectedAbsoluteUrl) . '"', $editContent);
        $this->assertStringContainsString('value="' . e($expectedAbsoluteUrl) . '"', $editContent);

        // 3. Show page resolves relative URL safely
        $showResponse = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.show', $entry->id) . '?back_url=' . urlencode($relativeUrl));
        $showResponse->assertStatus(200);
        $showContent = $showResponse->getContent();
        $this->assertStringContainsString('href="' . e($expectedAbsoluteUrl) . '"', $showContent);
        $this->assertStringContainsString(urlencode($expectedAbsoluteUrl), $showContent);

        // 4. Protocol-relative URLs are rejected
        $protocolRelative = '//evil.com/phish';
        $protoResponse = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'items' => $items,
            'back_url' => $protocolRelative,
        ]);
        $protoResponse->assertRedirect(route('journal_entry.index'));
    }

    /**
     * T4.11: Complex query strings (multi-account, department, dates, options) preserved in chained flow.
     */
    public function testComplexQueryStringsPreservedInChainedFlow()
    {
        $entry = $this->getTestJournalEntry();
        $detail = $entry->details->first();

        $complexBackUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $detail->kode_akun . ',1101001',
            'sort_by' => 'transaction_number',
            'show_comment' => 'transaction_comment',
        ]);

        // Show page receives complex back_url
        $showResponse = $this->actingAs($this->adminUser)
            ->get(route('journal_entry.show', $entry->id) . '?back_url=' . urlencode($complexBackUrl));
        $showResponse->assertStatus(200);

        // Edit page opened via Show link
        $editUrl = route('journal_entry.edit', $entry->id) . '?back_url=' . urlencode($complexBackUrl);
        $editResponse = $this->actingAs($this->adminUser)->get($editUrl);
        $editResponse->assertStatus(200);

        // Cancel button in Edit page retains complexBackUrl
        $this->assertStringContainsString('href="' . e($complexBackUrl) . '"', $editResponse->getContent());

        // Process update retains complexBackUrl
        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $updateResponse = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'items' => $items,
            'back_url' => $complexBackUrl,
        ]);

        $updateResponse->assertStatus(302);
        $updateResponse->assertRedirect($complexBackUrl);

        // Parse and compare all query parameters
        $redirectUrl = $updateResponse->headers->get('Location');
        parse_str(parse_url($complexBackUrl, PHP_URL_QUERY), $expectedParams);
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $actualParams);
        $this->assertEquals($expectedParams, $actualParams);
    }

    /**
     * T4.12: Running balance, debits, and credits in Buku Besar immediately reflect updated amounts.
     */
    public function testBukuBesarRunningBalanceDebitCreditRefreshesAfterUpdate()
    {
        $entry = $this->getTestJournalEntry();
        $detail = $entry->details->first();
        $originalDebit = $detail->debits;
        $originalCredit = $detail->credits;

        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $detail->kode_akun,
        ]);

        // 1. Initial view of Buku Besar
        $initialResponse = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $initialResponse->assertStatus(200);

        // 2. Perform update with modified amounts
        $newDebitAmount = '88888.00';
        $newCreditAmount = '88888.00';

        $items = [];
        $first = true;
        foreach ($entry->details as $d) {
            if ($first) {
                $items[] = [
                    'kode_akun' => $d->kode_akun,
                    'departemen_akun_id' => $d->departemen_akun_id,
                    'debits' => $newDebitAmount,
                    'credits' => '0',
                    'comment' => 'Amount refresh test debit',
                ];
                $first = false;
            } else {
                $items[] = [
                    'kode_akun' => $d->kode_akun,
                    'departemen_akun_id' => $d->departemen_akun_id,
                    'debits' => '0',
                    'credits' => $newCreditAmount,
                    'comment' => 'Amount refresh test credit',
                ];
                break; // 2 balanced items
            }
        }

        $updateResponse = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Amount refresh test',
            'items' => $items,
            'back_url' => $bukuBesarUrl,
        ]);

        $updateResponse->assertRedirect($bukuBesarUrl);

        // 3. Immediately fetch Buku Besar and assert new formatted amount appears
        $refreshedResponse = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $refreshedResponse->assertStatus(200);
        $formattedAmount = number_format(88888, 2, ',', '.');
        $this->assertStringContainsString($formattedAmount, $refreshedResponse->getContent(),
            "Buku Besar report must immediately reflect the updated debit/credit amounts");

        // Restore original values
        $restoreItems = [];
        foreach ($entry->details as $d) {
            $restoreItems[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }
        $restoreItems[0]['debits'] = (string) $originalDebit;
        $restoreItems[0]['credits'] = (string) $originalCredit;
        if (isset($restoreItems[1])) {
            $restoreItems[1]['debits'] = (string) $entry->details[1]->debits;
            $restoreItems[1]['credits'] = (string) $entry->details[1]->credits;
        }
        $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => $entry->comment,
            'items' => $restoreItems,
            'back_url' => $bukuBesarUrl,
        ]);
    }

    // =========================================================================
    // R6: MODAL QUICK PREVIEW & TAB CLOSING WORKFLOW
    // =========================================================================

    /**
     * T6.1: Modal footer contains both direct edit button and new tab button with rel="opener".
     */
    public function testModalContainsDirectEditAndNewTabButtons()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail);
        $entry = $detail->journalEntry;

        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'selected_accounts' => $detail->kode_akun,
        ]);

        $response = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('id="qvEditDirectLink"', $content,
            "Modal must contain a direct edit button (qvEditDirectLink)");
        $this->assertStringContainsString('id="qvEditLink"', $content,
            "Modal must contain a new tab edit button (qvEditLink)");
        $this->assertStringContainsString('rel="opener"', $content,
            "New tab link must declare rel=\"opener\" to enable opener communication and window.close()");
    }

    /**
     * T6.2: Journal entry update from new tab returns tab_close_redirect view.
     */
    public function testJournalEntryUpdateFromNewTabReturnsTabCloseRedirectView()
    {
        $entry = $this->getTestJournalEntry();
        $targetBackUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'selected_accounts' => '1102001',
        ]);

        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test updated via new tab ' . time(),
            'items' => $items,
            'back_url' => $targetBackUrl,
            'from_new_tab' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('window.close()', $content,
            "Tab close view must attempt to close the edit tab");
        $this->assertStringContainsString('window.opener.location.href = backUrl', $content,
            "Tab close view must navigate opener to target back_url");
        $this->assertStringContainsString('rca_journal_updated', $content,
            "Tab close view must broadcast localStorage event for cross-tab sync");
    }

    /**
     * T6.3: Journal entry update decodes HTML entity escaped back_url.
     */
    public function testJournalEntryUpdateDecodesHtmlEntityEscapedBackUrl()
    {
        $entry = $this->getTestJournalEntry();
        $escapedBackUrl = route('buku_besar.buku_besar_report') . '?start_date=2026-01-01&amp;end_date=2026-12-31&amp;selected_accounts=1102001';

        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test html entity decode ' . time(),
            'items' => $items,
            'back_url' => $escapedBackUrl,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $cleanBackUrl = route('buku_besar.buku_besar_report') . '?start_date=2026-01-01&end_date=2026-12-31&selected_accounts=1102001';
        $response->assertRedirect($cleanBackUrl);
    }

    /**
     * T6.4: Buku Besar report recovers query parameters with amp; prefix.
     */
    public function testBukuBesarRecoversHtmlEntityEscapedFilterParameters()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail);
        $entry = $detail->journalEntry;

        $response = $this->actingAs($this->adminUser)->get('/laporan/buku-besar?start_date=' . $entry->tanggal . '&amp;end_date=' . $entry->tanggal . '&amp;selected_accounts=' . $detail->kode_akun);
        $response->assertStatus(200);

        // When recovered properly, the report should render successfully without crashing or ignoring filters
        $this->assertStringContainsString($detail->kode_akun, $response->getContent());
    }

    /**
     * T6.5: Journal entry update falls back to HTTP referer header when back_url is omitted.
     */
    public function testJournalEntryUpdateFallsBackToRefererWhenBackUrlMissing()
    {
        $entry = $this->getTestJournalEntry();
        $refererUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'selected_accounts' => '1102001',
        ]);

        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test referer fallback ' . time(),
            'items' => $items,
        ];

        $response = $this->actingAs($this->adminUser)
            ->withHeaders(['Referer' => $refererUrl])
            ->put(route('journal_entry.update', $entry->id), $payload);

        $response->assertRedirect($refererUrl);
    }

    /**
     * T6.6: Quick View endpoint falls back to referer header when back_url query param is empty.
     */
    public function testQuickViewFallsBackToRefererWhenBackUrlMissing()
    {
        $entry = $this->getTestJournalEntry();
        $refererUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'selected_accounts' => '1102001',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withHeaders(['Referer' => $refererUrl])
            ->get(route('journal_entry.quick_view', $entry->id));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('edit_url', $data);
        $this->assertStringContainsString('back_url=', $data['edit_url']);
        $this->assertStringContainsString(urlencode('1102001'), $data['edit_url']);
    }

    /**
     * T6.7: Buku Besar report modal sets window.open handler for script-closable new tab.
     */
    public function testBukuBesarModalIncludesWindowOpenHandlerForTabLink()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail);
        $entry = $detail->journalEntry;

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'selected_accounts' => $detail->kode_akun,
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('window.open(editTabUrl, \'_blank\')', $content,
            "Buku Besar script must attach window.open handler to qvEditLink for script-closable tabs");
        $this->assertStringContainsString('rca_last_handled_time', $content,
            "Buku Besar script must track rca_last_handled_time in sessionStorage to prevent reload loops");
        $this->assertStringContainsString('rca_pending_toast', $content,
            "Buku Besar script must handle pending toast across tab updates");
    }

    /**
     * T6.8: Update with from_new_tab=1 renders tab_close_redirect view.
     */
    public function testUpdateFromNewTabReturnsTabCloseRedirectView()
    {
        $entry = $this->getTestJournalEntry();
        $targetBukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'selected_accounts' => '1102001',
        ]);

        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'New tab update test',
            'items' => $items,
            'back_url' => $targetBukuBesarUrl,
            'from_new_tab' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertStatus(200);
        $response->assertViewIs('journal_entry.tab_close_redirect');
        $response->assertViewHas('backUrl', $targetBukuBesarUrl);
        $content = $response->getContent();
        $this->assertStringContainsString('rca_journal_updated', $content);
        $this->assertStringContainsString('window.close()', $content);
    }

    /**
     * T6.9: Repeatedly nested &amp;amp; entities in back_url are completely resolved.
     */
    public function testNestedHtmlEntitiesInBackUrlAreCompletelyResolved()
    {
        $entry = $this->getTestJournalEntry();
        $nestedUrl = route('buku_besar.buku_besar_report') . '?start_date=2026-01-01&amp;amp;end_date=2026-12-31&amp;amp;selected_accounts=1102001';

        $items = [];
        foreach ($entry->details as $d) {
            $items[] = [
                'kode_akun' => $d->kode_akun,
                'departemen_akun_id' => $d->departemen_akun_id,
                'debits' => (string) $d->debits,
                'credits' => (string) $d->credits,
                'comment' => $d->comment,
            ];
        }

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Nested amp test',
            'items' => $items,
            'back_url' => $nestedUrl,
            'from_new_tab' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertStatus(302);
        $cleanExpectedUrl = route('buku_besar.buku_besar_report') . '?start_date=2026-01-01&end_date=2026-12-31&selected_accounts=1102001';
        $response->assertRedirect($cleanExpectedUrl);
    }

    /**
     * T6.10: Edit form correctly preserves from_new_tab and initializes Cancel fallback.
     */
    public function testEditFormPreservesFromNewTabAndInitializesCancelFallback()
    {
        $entry = $this->getTestJournalEntry();
        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', [
            $entry->id,
            'from_new_tab' => '1',
            'back_url' => route('buku_besar.buku_besar_report'),
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="from_new_tab" id="from_new_tab" value="1"', $content);
        $this->assertStringContainsString('handleCancel(event)', $content);
    }
}
