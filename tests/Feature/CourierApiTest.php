<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budiono Hadi Agung',
            'email' => 'budiono@example.com',
            'phone' => '081234567890',
            'vehicle_type' => 'motorcycle',
            'license_plate' => 'B 1234 XYZ',
            'level' => 3,
            'is_active' => true,
        ], $overrides);
    }

    // ---- index ----

    public function test_index_is_paginated(): void
    {
        Courier::factory()->count(20)->create();

        $this->getJson('/api/couriers?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_index_uses_default_page_size_of_15(): void
    {
        Courier::factory()->count(20)->create();

        $this->getJson('/api/couriers')
            ->assertOk()
            ->assertJsonCount(15, 'data');
    }

    public function test_index_is_sorted_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Charlie']);
        Courier::factory()->create(['name' => 'Alice']);
        Courier::factory()->create(['name' => 'Bob']);

        $this->getJson('/api/couriers')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.1.name', 'Bob')
            ->assertJsonPath('data.2.name', 'Charlie');
    }

    public function test_index_can_sort_by_registration_date(): void
    {
        $oldest = Courier::factory()->create(['name' => 'Zed', 'created_at' => now()->subDays(3)]);
        $middle = Courier::factory()->create(['name' => 'Amy', 'created_at' => now()->subDays(2)]);
        $newest = Courier::factory()->create(['name' => 'Mia', 'created_at' => now()->subDay()]);

        $this->getJson('/api/couriers?sort=created_at')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$oldest->id, $middle->id, $newest->id]);

        $this->getJson('/api/couriers?sort=-created_at')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$newest->id, $middle->id, $oldest->id]);
    }

    public function test_index_search_matches_partial_words_in_any_order(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        Courier::factory()->create(['name' => 'Siti Agung']);

        $this->getJson('/api/couriers?search=budi+agung')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');

        $this->getJson('/api/couriers?search=agung+budi')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_search_treats_wildcards_as_plain_text(): void
    {
        Courier::factory()->create(['name' => 'Budi']);

        $this->getJson('/api/couriers?search=%25')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_filters_by_levels(): void
    {
        foreach ([1, 2, 3, 4, 5] as $level) {
            Courier::factory()->level($level)->create();
        }

        $response = $this->getJson('/api/couriers?level=2,3')->assertOk();

        $response->assertJsonCount(2, 'data');
        $this->assertEqualsCanonicalizing([2, 3], $response->json('data.*.level'));
    }

    public function test_index_filter_and_search_can_be_combined(): void
    {
        Courier::factory()->level(2)->create(['name' => 'Budi Santoso']);
        Courier::factory()->level(5)->create(['name' => 'Budi Hartono']);

        $this->getJson('/api/couriers?search=budi&level=2,3')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budi Santoso');
    }

    public function test_index_rejects_invalid_query_parameters(): void
    {
        $this->getJson('/api/couriers?level=9')->assertUnprocessable()->assertJsonValidationErrors('level');
        $this->getJson('/api/couriers?level=abc')->assertUnprocessable()->assertJsonValidationErrors('level');
        $this->getJson('/api/couriers?sort=email')->assertUnprocessable()->assertJsonValidationErrors('sort');
        $this->getJson('/api/couriers?per_page=1000')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    // ---- show ----

    public function test_show_returns_all_courier_data(): void
    {
        $courier = Courier::factory()->create($this->validPayload());

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.email', 'budiono@example.com')
            ->assertJsonPath('data.phone', '081234567890')
            ->assertJsonPath('data.vehicle_type', 'motorcycle')
            ->assertJsonPath('data.license_plate', 'B 1234 XYZ')
            ->assertJsonPath('data.level', 3)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);
    }

    public function test_show_returns_404_for_unknown_courier(): void
    {
        $this->getJson('/api/couriers/999')->assertNotFound();
    }

    // ---- store ----

    public function test_store_saves_courier_to_database(): void
    {
        $this->postJson('/api/couriers', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Budiono Hadi Agung');

        $this->assertDatabaseHas('couriers', [
            'name' => 'Budiono Hadi Agung',
            'email' => 'budiono@example.com',
            'vehicle_type' => 'motorcycle',
            'level' => 3,
        ]);
    }

    #[DataProvider('invalidStorePayloads')]
    public function test_store_validates_input(string $field, mixed $value): void
    {
        $this->postJson('/api/couriers', $this->validPayload([$field => $value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('couriers', 0);
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function invalidStorePayloads(): array
    {
        return [
            'name missing' => ['name', null],
            'name too long' => ['name', str_repeat('a', 256)],
            'email missing' => ['email', null],
            'email invalid' => ['email', 'not-an-email'],
            'phone missing' => ['phone', null],
            'phone invalid' => ['phone', 'abc123'],
            'vehicle type missing' => ['vehicle_type', null],
            'vehicle type unknown' => ['vehicle_type', 'rocket'],
            'level missing' => ['level', null],
            'level too low' => ['level', 0],
            'level too high' => ['level', 6],
            'level not a number' => ['level', 'high'],
            'is_active not boolean' => ['is_active', 'maybe'],
        ];
    }

    public function test_store_rejects_duplicate_email(): void
    {
        Courier::factory()->create(['email' => 'budiono@example.com']);

        $this->postJson('/api/couriers', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    // ---- update ----

    public function test_update_saves_changes_to_database(): void
    {
        $courier = Courier::factory()->level(1)->create();

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['level' => 5]))
            ->assertOk()
            ->assertJsonPath('data.level', 5);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'Budiono Hadi Agung',
            'level' => 5,
        ]);
    }

    public function test_update_allows_partial_changes(): void
    {
        $courier = Courier::factory()->create(['name' => 'Original', 'level' => 1]);

        $this->patchJson("/api/couriers/{$courier->id}", ['level' => 4])->assertOk();

        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Original', 'level' => 4]);
    }

    public function test_update_allows_keeping_own_email(): void
    {
        $courier = Courier::factory()->create(['email' => 'keep@example.com']);

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['email' => 'keep@example.com']))
            ->assertOk();
    }

    public function test_update_rejects_email_used_by_another_courier(): void
    {
        Courier::factory()->create(['email' => 'taken@example.com']);
        $courier = Courier::factory()->create();

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['email' => 'taken@example.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_update_validates_input_and_leaves_database_unchanged(): void
    {
        $courier = Courier::factory()->level(2)->create();

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['level' => 99]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('level');

        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'level' => 2]);
    }

    public function test_update_returns_404_for_unknown_courier(): void
    {
        $this->putJson('/api/couriers/999', $this->validPayload())->assertNotFound();
    }

    // ---- destroy ----

    public function test_destroy_removes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();

        $this->deleteJson("/api/couriers/{$courier->id}")->assertNoContent();

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
    }

    public function test_destroy_returns_404_for_unknown_courier(): void
    {
        $this->deleteJson('/api/couriers/999')->assertNotFound();
    }
}
