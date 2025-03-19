<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class JobBoardTest extends PantherTestCase
{
    public function testJobListDisplaysCorrectly(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

      // Ověření načtení seznamu
        $this->assertSelectorExists('.job-list');
        $this->assertSelectorExists('.job-card');

      // Ověření obsahu karty
        $firstJob = $crawler->filter('.job-card')->first();
        $this->assertStringContainsString('PHP Developer', $firstJob->text());
        $this->assertSelectorExists('.job-location');
        $this->assertSelectorExists('.job-salary');
    }

    public function testJobListPaginationWorks(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

      // Kliknutí na druhou stránku
        $client->clickLink('2');

      // Ověření změny stránky
        $this->assertSelectorTextContains('.pagination .active', '2');
        $this->assertSelectorExists('.job-card');
    }

    public function testBackToListButtonWorks(): void
    {
        $client = static::createPantherClient();

      // Přejít na detail
        $crawler = $client->request('GET', '/');
        $client->clickLink('Zobrazit detail');

      // Kliknout na tlačítko zpět
        $client->clickLink('Zpět na seznam');

      // Ověřit návrat na seznam
        $this->assertSelectorExists('.job-list');
    }

    public function testJobDetailDisplaysCorrectly(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/jobs/1');

      // Ověření obsahu detailu
        $this->assertSelectorExists('h1');
        $this->assertSelectorExists('.job-description');
        $this->assertSelectorExists('.job-location');
        $this->assertSelectorExists('.job-salary');

      // Ověření formuláře
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('#name');
        $this->assertSelectorExists('#email');
        $this->assertSelectorExists('#phone');
    }

    public function testSuccessfulApplicationSubmission(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/jobs/1');

      // Vyplnění formuláře
        $client->submitForm('Odeslat odpověď', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+420123456789',
        'cover_letter' => 'I am interested in this position',
        'salary[amount]' => '50000',
        'salary[currency]' => 'CZK',
        'salary[unit]' => 'month',
        'salary[type]' => '0',
        'gdpr_agreement' => true
        ]);

      // Ověření úspěšného odeslání
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Vaše odpověď byla úspěšně odeslána');
    }

    public function testFailedApplicationSubmission(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/jobs/1');

      // Odeslání prázdného formuláře
        $client->submitForm('Odeslat odpověď', []);

      // Ověření chybových hlášek
        $this->assertSelectorExists('.is-invalid');
        $this->assertSelectorTextContains('.invalid-feedback', 'Jméno a příjmení jsou povinná');
    }
}
