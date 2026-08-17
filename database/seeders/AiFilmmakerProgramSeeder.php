<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PartnershipProgram;
use App\Models\CommissionRule;
use App\Models\ProgramProduct;
use Illuminate\Database\Seeder;

class AiFilmmakerProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates the AI Filmmaking Masterclass program with:
     * - Product: AI Filmmaking Masterclass (₦20,000)
     * - Commission rules:
     *   - Level 1 (Direct): 20%
     *   - Level 2 (Parent): 5%
     */
    public function run(): void
    {
        // Create product
        $product = Product::firstOrCreate(
            ['slug' => 'ai-filmmaking-masterclass'],
            [
                'name' => 'AI Filmmaking Masterclass',
                'description' => 'Learn professional video filmmaking using AI-powered tools and techniques. Master the skills needed to create stunning video content and monetize your creativity.',
                'price' => 20000.00,
                'currency' => 'NGN',
                'status' => 'active',
            ]
        );

        // Create partnership program
        $program = PartnershipProgram::firstOrCreate(
            ['slug' => 'ai-filmmaking-partnership'],
            [
                'name' => 'AI Filmmaking Partnership Program',
                'description' => 'Earn commissions by promoting the AI Filmmaking Masterclass. Direct sales earn 20% commission, and recruit other partners to earn 5% on their sales.',
                'status' => 'active',
                'attribution_window_days' => 30,
                'minimum_payout' => 5000,
            ]
        );

        // Attach product to program
        ProgramProduct::firstOrCreate(
            [
                'program_id' => $program->id,
                'product_id' => $product->id,
            ]
        );

        // Create commission rule for Level 1 (Direct seller)
        CommissionRule::firstOrCreate(
            [
                'program_id' => $program->id,
                'level' => 1,
                'event' => 'sale',
            ],
            [
                'product_id' => null,
                'commission_type' => 'percentage',
                'value' => 20, // 20%
                'status' => true,
                'priority' => 1,
            ]
        );

        // Create commission rule for Level 2 (Parent/Recruiter)
        CommissionRule::firstOrCreate(
            [
                'program_id' => $program->id,
                'level' => 2,
                'event' => 'sale',
            ],
            [
                'product_id' => null,
                'commission_type' => 'percentage',
                'value' => 5, // 5%
                'status' => true,
                'priority' => 1,
            ]
        );

        $this->command->info('AI Filmmaking program and commission rules created successfully!');
    }
}
