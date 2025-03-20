<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class JobBoardTest extends PantherTestCase
{
  private ?Client $client = null;

  protected function setUp(): void
  {
    $this->client = static::createPantherClient([
      'external_base_uri' => 'http://localhost:5173',
      'browser' => static::CHROME,
    ]);
  }

  protected function tearDown(): void
  {
    if ($this->client) {
      // Check for JavaScript errors
      $logs = $this->client->getWebDriver()->manage()->getLog('browser');
      foreach ($logs as $log) {
        if ($log['level'] === 'SEVERE') {
          $this->fail('JavaScript error: ' . $log['message']);
        }
      }
    }

    parent::tearDown();
  }

  /**
   * @throws NoSuchElementException
   * @throws TimeoutException
   */
  public function testJobListDisplaysCorrectly(): void
  {
    $this->client->request('GET', '/');

    // Wait for content to load
    $this->client->waitForVisibility('.job-card', 10);

    // Check for job list elements
    $this->assertSelectorExists('.el-card');
    $this->assertSelectorExists('.el-descriptions');
    $this->assertSelectorExists('.el-tag');
  }

  /**
   * @throws NoSuchElementException
   * @throws TimeoutException
   */
  public function testJobListPaginationWorks(): void
  {
    $this->client->request('GET', '/');
    $this->client->waitForVisibility('.el-pagination', 10);

    $this->assertSelectorExists('.el-pagination');
    $this->assertSelectorExists('.el-card');
  }

  /**
   * @throws NoSuchElementException
   * @throws TimeoutException
   */
  public function testBackToListButtonWorks(): void
  {
    $this->client->request('GET', '/jobs/431912');
    $this->client->waitForVisibility('.btn-outline-primary', 10);

    $this->assertSelectorExists('.btn-outline-primary');
  }

  /**
   * @throws NoSuchElementException
   * @throws TimeoutException
   */
  public function testJobDetailDisplaysCorrectly(): void
  {
    $this->client->request('GET', '/jobs/431912');
    $this->client->waitForVisibility('form', 10);

    $this->assertSelectorExists('h1');
    $this->assertSelectorExists('.card-text');

    $this->assertSelectorExists('form');
    $this->assertSelectorExists('#name');
    $this->assertSelectorExists('#email');
    $this->assertSelectorExists('#phone');
  }
}