<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ConsultantProfile;
use App\Models\ConsultantSchedule;
use App\Models\Review;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // System Settings
        SystemSetting::set('cancel_policy_hours', '24', 'キャンセルポリシー（時間前）');
        SystemSetting::set('booking_slot_duration', '60', 'デフォルトスロット時間（分）');
        SystemSetting::set('max_bookings_per_day', '8', '1日あたり最大予約数');
        SystemSetting::set('google_calendar_enabled', '0', 'Googleカレンダー連携');

        // Admin user
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '090-0000-0000',
            'is_active' => true,
        ]);

        // Consultants
        $consultants = [
            [
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'specialty' => '経営戦略',
                'bio' => '大手コンサルティングファームで15年の経験を持つ経営戦略の専門家です。中小企業から大企業まで、幅広い業界でのコンサルティング実績があります。',
                'hourly_rate' => 15000,
                'experience_years' => 15,
                'qualifications' => ['MBA', '中小企業診断士'],
                'languages' => ['日本語', '英語'],
            ],
            [
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'specialty' => 'ITコンサルティング',
                'bio' => 'IT戦略立案からシステム導入支援まで、デジタルトランスフォーメーションを包括的にサポートします。AWS認定ソリューションアーキテクトの資格を保持。',
                'hourly_rate' => 12000,
                'experience_years' => 10,
                'qualifications' => ['AWS認定ソリューションアーキテクト', 'PMP'],
                'languages' => ['日本語', '英語', '中国語'],
            ],
            [
                'name' => '鈴木一郎',
                'email' => 'suzuki@example.com',
                'specialty' => '人事・組織開発',
                'bio' => '組織開発、人材育成、採用戦略のスペシャリスト。100社以上の組織改革プロジェクトを手掛けてきました。',
                'hourly_rate' => 10000,
                'experience_years' => 8,
                'qualifications' => ['キャリアコンサルタント', '社会保険労務士'],
                'languages' => ['日本語'],
            ],
            [
                'name' => '高橋美咲',
                'email' => 'takahashi@example.com',
                'specialty' => 'マーケティング',
                'bio' => 'デジタルマーケティング戦略からブランディングまで、売上向上のための包括的なマーケティングコンサルティングを提供します。',
                'hourly_rate' => 11000,
                'experience_years' => 12,
                'qualifications' => ['Google広告認定資格', 'ウェブ解析士'],
                'languages' => ['日本語', '英語'],
            ],
            [
                'name' => '山田健太',
                'email' => 'yamada@example.com',
                'specialty' => '財務・会計',
                'bio' => '財務分析、資金調達、事業計画策定の専門家。スタートアップから上場企業まで幅広くサポートしています。',
                'hourly_rate' => 13000,
                'experience_years' => 20,
                'qualifications' => ['公認会計士', '税理士'],
                'languages' => ['日本語', '英語'],
            ],
        ];

        foreach ($consultants as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'consultant',
                'is_active' => true,
            ]);

            ConsultantProfile::create([
                'user_id' => $user->id,
                'specialty' => $data['specialty'],
                'bio' => $data['bio'],
                'hourly_rate' => $data['hourly_rate'],
                'experience_years' => $data['experience_years'],
                'qualifications' => $data['qualifications'],
                'languages' => $data['languages'],
                'auto_approve' => true,
                'is_featured' => true,
            ]);

            // Create schedules for next 5 weekdays
            for ($day = 1; $day <= 7; $day++) {
                $date = Carbon::now()->addDays($day);
                if ($date->isWeekend()) continue;

                $slots = [
                    ['10:00', '11:00'],
                    ['14:00', '15:00'],
                ];

                foreach ($slots as $slot) {
                    ConsultantSchedule::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                        'is_available' => true,
                    ]);
                }
            }
        }

        // Regular users
        $users = [
            ['name' => '山本太郎', 'email' => 'yamamoto@example.com'],
            ['name' => '中村美香', 'email' => 'nakamura@example.com'],
            ['name' => '小林健一', 'email' => 'kobayashi@example.com'],
            ['name' => '加藤愛', 'email' => 'kato@example.com'],
            ['name' => '吉田誠', 'email' => 'yoshida@example.com'],
        ];

        $createdUsers = [];
        foreach ($users as $data) {
            $createdUsers[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]);
        }

        // Sample bookings
        $consultantUsers = User::where('role', 'consultant')->get();
        $statuses = ['completed', 'approved', 'pending'];

        foreach ($createdUsers as $index => $user) {
            $consultant = $consultantUsers[$index % $consultantUsers->count()];
            $schedules = $consultant->schedules()->where('date', '>=', now()->toDateString())->limit(3)->get();

            foreach ($schedules as $i => $schedule) {
                $status = $statuses[$i % count($statuses)];
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'consultant_id' => $consultant->id,
                    'schedule_id' => $schedule->id,
                    'booking_date' => $schedule->date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $status,
                    'amount' => $consultant->consultantProfile->hourly_rate,
                ]);

                // Add reviews for completed bookings
                if ($status === 'completed') {
                    $rating = rand(3, 5);
                    Review::create([
                        'user_id' => $user->id,
                        'consultant_id' => $consultant->id,
                        'booking_id' => $booking->id,
                        'rating' => $rating,
                        'comment' => $this->getRandomComment($rating),
                    ]);

                    $consultant->consultantProfile->updateRating();
                }
            }
        }
    }

    private function getRandomComment(int $rating): string
    {
        $comments = [
            5 => [
                '非常に丁寧で分かりやすい説明でした。実践的なアドバイスをいただき、大変参考になりました。',
                '期待以上の内容で、すぐに実務に活かせるアドバイスをいただきました。また相談したいです。',
            ],
            4 => [
                '的確なアドバイスをいただき、課題の整理ができました。フォローアップもしっかりしていました。',
                '経験に基づいた実践的なアドバイスが非常に参考になりました。',
            ],
            3 => [
                '基本的な相談には満足できましたが、もう少し具体的な提案が欲しかったです。',
                '全体的に良い相談でしたが、時間が少し足りなかった印象です。',
            ],
        ];

        $pool = $comments[$rating] ?? $comments[4];
        return $pool[array_rand($pool)];
    }
}
