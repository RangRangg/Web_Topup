<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\JokiService;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that homepage renders successfully with HTTP 200.
     */
    public function test_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Aethereal Joki');
    }

    /**
     * Test hero section copy contains top up, jasa joki, AI assistant, and required CTAs.
     */
    public function test_hero_section_has_required_copy_and_ctas(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Top Up Game');
        $response->assertSee('Jasa Joki');
        $response->assertSee('AI Assistant');
        $response->assertSee('Top Up Sekarang');
        $response->assertSee('Cari Jasa Joki');
    }

    /**
     * Test database elements render in their corresponding sections.
     */
    public function test_homepage_renders_database_driven_sections(): void
    {
        $game = Game::where('is_active', true)->first();
        if ($game) {
            $response = $this->get('/');
            $response->assertSee($game->name);
        }

        $promo = Promotion::where('is_active', true)->first();
        if ($promo) {
            $response = $this->get('/');
            $response->assertSee($promo->code);
        }

        $service = JokiService::where('is_active', true)->where('is_popular', true)->first();
        if ($service) {
            $response = $this->get('/');
            $response->assertSee($service->name);
        }
    }

    /**
     * Test that all essential sections are present in the response.
     */
    public function test_homepage_contains_all_essential_sections(): void
    {
        $response = $this->get('/');

        // Section 1: Hero Carousel Banner
        $response->assertSee('EXQUISITE');
        $response->assertSee('COLLECTION');

        // Section 2: Rekomendasi
        $response->assertSee('REKOMENDASI');

        // Section 3: Populer Sekarang
        $response->assertSee('POPULER SEKARANG!');

        // Section 4: Game Catalog & Category Pills
        $response->assertSee('Top Up Games');
        $response->assertSee('Tampilkan Lainnya...');

        // Section 5: Artikel Terbaru
        $response->assertSee('ARTIKEL TERBARU &amp; BERITA GAME', false);

        // Section 6: Footer
        $response->assertSee('Metode Pembayaran Resmi');
        $response->assertSee('Aethereal');
    }
}
