<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Fund;
use App\Models\FundManager;
use App\Models\Company;

class FundControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate --seed');
    }
    /**
     * A basic feature test example.
     */
    public function test_can_get_all_funds(): void
    {
        // Act: Make a GET request to the funds index endpoint
        $response = $this->getJson('/api/v1/funds');

        // Assert: Check response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'start_year',
                        'fund_manager_id',
                        'created_at',
                        'updated_at',
                        'fund_manager',
                        'fund_aliases',
                        'companies',
                    ],
                ],
                'links',
                'next_page_url',
            ]);
    }

    /**
     * Test the store method.
     *
     * @return void
     */
    public function test_store_creates_a_new_fund()
    {
        // Arrange: Create a fund manager and companies
        $fundManager = FundManager::factory()->create();
        $companies = Company::factory()->count(2)->create();

        // Prepare request data
        $data = [
            'name' => $this->faker->company,
            'start_year' => $this->faker->year,
            'fund_manager_id' => $fundManager->id,
            'aliases' => [
                $this->faker->company . ' Alias 1',
                $this->faker->company . ' Alias 2',
            ],
            'companies' => $companies->pluck('id')->toArray(),
        ];

        // Act: Send POST request to the store route
        $response = $this->postJson('/api/v1/funds', $data);

        // Assert: Check response status and data
        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => $data['name'],
                'start_year' => $data['start_year'],
                'fund_manager_id' => $fundManager->id,
            ]);

        // Assert that the fund was created in the database
        $this->assertDatabaseHas('funds', [
            'name' => $data['name'],
            'start_year' => $data['start_year'],
            'fund_manager_id' => $fundManager->id,
        ]);

        // Assert that aliases and companies were associated
        $fund = Fund::where('name', $data['name'])->first();
        $this->assertCount(2, $fund->fundAliases);
        $this->assertCount(2, $fund->companies);
    }

    /**
     * Test the show method.
     *
     * @return void
     */
    public function test_show_returns_a_fund()
    {
        $fund = Fund::get()->random();
        // Act: Send GET request to the show route
        $response = $this->getJson("/api/v1/funds/{$fund->id}");

        // Assert: Check response status and data
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $fund->id,
                'name' => $fund->name,
            ])
            ->assertJsonStructure([
                'id',
                'name',
                'start_year',
                'fund_manager_id',
                'fund_manager',
                'fund_aliases',
                'companies',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Test the update method.
     *
     * @return void
     */
    public function test_update_modifies_a_fund()
    {
        // Arrange: Create a fund and related data
        $fund = Fund::get()->random();

        $newFundManager = FundManager::factory()->create();
        $newCompanies = Company::factory()->count(3)->create();

        // Prepare updated data
        $data = [
            'name' => 'Updated Fund Name',
            'start_year' => rand(1900, date('Y')),
            'fund_manager_id' => $newFundManager->id,
            'aliases' => [
                'Updated Alias 1',
                'Updated Alias 2',
            ],
            'companies' => $newCompanies->pluck('id')->toArray(),
        ];

        // Act: Send PUT request to the update route
        $response = $this->putJson("/api/v1/funds/{$fund->id}", $data);

        // Assert: Check response status and data
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $fund->id,
                'name' => $data['name'],
                'start_year' => $data['start_year'],
                'fund_manager_id' => $newFundManager->id,
            ]);

        // Assert that the fund was updated in the database
        $this->assertDatabaseHas('funds', [
            'id' => $fund->id,
            'name' => $data['name'],
            'start_year' => $data['start_year'],
            'fund_manager_id' => $newFundManager->id,
        ]);

        // Assert that aliases and companies were updated
        $fund->refresh();
        $this->assertCount(2, $fund->fundAliases);
        $this->assertEquals('Updated Alias 1', $fund->fundAliases[0]->name);
        $this->assertCount(3, $fund->companies);
    }

    /**
     * Test the destroy method.
     *
     * @return void
     */
    public function test_destroy_deletes_a_fund()
    {
        // Arrange: Create a fund
        $fund = Fund::factory()->create();

        // Act: Send DELETE request to the destroy route
        $response = $this->deleteJson("/api/v1/funds/{$fund->id}");

        // Assert: Check response status
        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Resource deleted successfully!',
            ]);

        // Assert that the fund was deleted from the database
        $this->assertDatabaseMissing('funds', [
            'id' => $fund->id,
        ]);
    }
}
