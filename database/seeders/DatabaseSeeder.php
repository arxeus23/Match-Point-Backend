<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Job;
use App\Models\Organization;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Basic', 'slug' => 'basic', 'price_paise' => 49900, 'features' => ['dashboard', 'jobs', 'candidates', 'applications', 'resumes'], 'support_months' => 0, 'ai_level' => 'none'],
            ['name' => 'Plus', 'slug' => 'plus', 'price_paise' => 99900, 'features' => ['dashboard', 'jobs', 'candidates', 'applications', 'resumes', 'manual_ai_questions'], 'support_months' => 1, 'ai_level' => 'limited'],
            ['name' => 'Pro', 'slug' => 'pro', 'price_paise' => 1599900, 'features' => ['dashboard', 'jobs', 'candidates', 'applications', 'resumes', 'automated_ai', 'extended_ai'], 'support_months' => 12, 'ai_level' => 'extended'],
        ];
        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }
        PlatformSetting::firstOrCreate([], ['platform_name' => 'MatchPoint HR', 'timezone' => 'Asia/Kolkata']);
        $organization = Organization::updateOrCreate(['slug' => 'acme-corp'], ['name' => 'Acme Corporation', 'plan' => 'growth', 'subscription_plan_id' => SubscriptionPlan::where('slug', 'pro')->value('id'), 'subscription_starts_at' => now(), 'subscription_ends_at' => now()->addMonth(), 'status' => 'active']);
        $engineering = Department::firstOrCreate(['organization_id' => $organization->id, 'name' => 'Engineering']);
        User::updateOrCreate(['email' => 'admin@matchpoint.test'], ['organization_id' => $organization->id, 'name' => 'MatchPoint Super Admin', 'password' => 'password', 'role' => 'super_admin', 'status' => 'active']);
        User::updateOrCreate(['email' => 'recruiter@matchpoint.test'], ['organization_id' => $organization->id, 'name' => 'Demo Recruiter', 'password' => 'password', 'role' => 'recruiter', 'status' => 'active']);
        Job::updateOrCreate(['organization_id' => $organization->id, 'title' => 'Senior Laravel Engineer'], ['department_id' => $engineering->id, 'description' => 'Build resilient hiring infrastructure.', 'status' => 'open', 'location' => 'Remote', 'required_skills' => ['Laravel', 'PHP', 'PostgreSQL', 'Redis']]);
        Candidate::updateOrCreate(['organization_id' => $organization->id, 'email' => 'alex@example.com'], ['first_name' => 'Alex', 'last_name' => 'Morgan', 'current_title' => 'Backend Engineer', 'experience_years' => 6, 'skills' => ['Laravel', 'PHP', 'PostgreSQL'], 'source' => 'referral']);
    }
}
