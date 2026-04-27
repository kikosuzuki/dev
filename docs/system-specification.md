# YCS コンサルタント予約システム — システム仕様書

**作成日:** 2026年4月22日
**バージョン:** 1.0

---

## 1. システム構成

### 1.1 技術スタック

| 項目 | 技術 |
|------|------|
| 言語 | PHP 8.x |
| フレームワーク | Laravel 12 |
| フロントエンド | Blade テンプレート + Alpine.js + Tailwind CSS |
| データベース | SQLite（開発）/ MySQL（本番） |
| メール送信 | SMTP（Xserver） |
| セッション / キュー / キャッシュ | Database ドライバ |

### 1.2 外部API連携

| サービス | API | 用途 |
|----------|-----|------|
| Google Calendar | Google Calendar API v3 (OAuth 2.0) | イベント作成・削除・重複チェック |
| LINE | LINE Messaging API | プッシュメッセージ通知 |
| Chatwork | Chatwork API | ルームメッセージ送信・メンバー取得 |

### 1.3 本番環境

| 項目 | 値 |
|------|------|
| ホスティング | Xserver |
| アプリケーションパス | `~/ycscampaign.com/public_html/consul/` |
| 公開URL | `https://ycscampaign.com/consul/consultation` |
| タイムゾーン | Asia/Tokyo |
| ロケール | ja |

---

## 2. データベース設計

### 2.1 ER図（主要リレーション）

```
users ─┬── consultant_profiles (1:1)
       ├── consultant_schedules (1:N) ── bookings (1:N)
       ├── bookings as user_id (1:N)
       ├── bookings as consultant_id (1:N)
       ├── reviews as user_id (1:N)
       ├── reviews as consultant_id (1:N)
       └── favorites (M:N pivot)

bookings ── reviews (1:1)
bookings ── notification_logs (1:N)
```

### 2.2 テーブル定義

#### users

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| name | varchar | NO | 氏名 |
| email | varchar | NO | メール（一意） |
| password | varchar | NO | ハッシュ化パスワード |
| role | enum(user,consultant,admin) | NO | ロール |
| phone | varchar | YES | 電話番号 |
| avatar | varchar | YES | アバター画像パス |
| line_user_id | varchar | YES | LINE ユーザーID |
| chatwork_id | varchar | YES | Chatwork アカウントID |
| chatwork_room_id | varchar | YES | Chatwork ルームID |
| notify_email | boolean | NO | メール通知 ON/OFF |
| notify_line | boolean | NO | LINE 通知 ON/OFF |
| notify_chatwork | boolean | NO | Chatwork 通知 ON/OFF |
| is_active | boolean | NO | アカウント有効/無効 |
| admin_notes | text | YES | 管理者メモ |
| user_type | varchar | YES | 種別（member/consultation） |
| email_verified_at | timestamp | YES | メール認証日時 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

#### consultant_profiles

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | |
| specialty | varchar | YES | 専門分野 |
| bio | text | YES | 自己紹介 |
| hourly_rate | decimal | YES | 時給 |
| experience_years | integer | YES | 経験年数 |
| photo | varchar | YES | プロフィール写真パス |
| qualifications | json | YES | 資格（配列） |
| languages | json | YES | 対応言語（配列） |
| meeting_url | varchar | YES | ミーティングURL |
| important_document_url | varchar | YES | 重要事項説明書URL |
| chatwork_account_id | varchar | YES | Chatwork アカウントID（メンション用） |
| google_refresh_token | varchar | YES | Google OAuth リフレッシュトークン |
| google_calendar_email | varchar | YES | Google カレンダーメール |
| google_calendar_id | varchar | YES | 同期先カレンダーID |
| google_conflict_calendar_ids | json | YES | 重複チェック用カレンダーID（配列） |
| is_featured | boolean | NO | おすすめ表示フラグ |
| booking_acceptance_enabled | boolean | NO | 予約受付 ON/OFF |
| average_rating | decimal(3,2) | NO | 平均評価 |
| total_reviews | integer | NO | レビュー総数 |
| total_bookings | integer | NO | 予約総数 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

#### consultant_schedules

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | コンサルタント |
| date | date | NO | 日付 |
| start_time | time | NO | 開始時間 |
| end_time | time | NO | 終了時間 |
| is_available | boolean | NO | 利用可能フラグ |
| calendar_blocked | boolean | NO | カレンダー重複ブロック |
| calendar_blocked_reason | varchar | YES | ブロック理由 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

**インデックス:** `[user_id, date]`, `[date, is_available]`

#### bookings

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | YES（ゲストはNULL） |
| consultant_id | bigint | FK→users | NO |
| schedule_id | bigint | FK→consultant_schedules | NO |
| booking_date | date | NO | 予約日 |
| start_time | time | NO | 開始時間 |
| end_time | time | NO | 終了時間 |
| status | enum(approved,completed,cancelled) | NO | ステータス |
| notes | text | YES | 備考 |
| cancel_reason | text | YES | キャンセル理由 |
| google_event_id | varchar | YES | 管理者 Google イベントID |
| admin_google_calendar_id | varchar | YES | 作成時の管理者カレンダーID |
| consultant_google_event_id | varchar | YES | コンサルタント Google イベントID |
| consultant_google_calendar_id | varchar | YES | 作成時のコンサルタントカレンダーID |
| meeting_url | varchar | YES | ミーティングURL |
| amount | decimal | YES | 金額 |
| is_guest | boolean | NO | ゲスト予約フラグ |
| guest_name | varchar | YES | ゲスト氏名 |
| guest_email | varchar | YES | ゲストメール |
| guest_phone | varchar | YES | ゲスト電話番号 |
| guest_referrer | varchar | YES | ゲスト紹介元 |
| consultation_result | varchar | YES | 相談結果（成功/失敗/保留） |
| consultation_notes | text | YES | 相談メモ |
| important_document_issued | boolean | NO | 重要事項説明書発行フラグ |
| consultation_record_reset_at | timestamp | YES | 相談記録リセット日時 |
| admin_notes | text | YES | 管理者メモ |
| reminder_day_before_sent | boolean | NO | 前日リマインダー送信済み |
| reminder_day_of_sent | boolean | NO | 当日リマインダー送信済み |
| reminder_10min_sent | boolean | NO | 直前リマインダー送信済み |
| morning_chatwork_sent | boolean | NO | 朝の Chatwork 送信済み |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

**インデックス:** `[user_id, status]`, `[consultant_id, status]`, `[booking_date, status]`

#### reviews

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | レビュー投稿者 |
| consultant_id | bigint | FK→users | 対象コンサルタント |
| booking_id | bigint | FK→bookings | 対象予約 |
| rating | integer | NO | 評価（1〜5） |
| comment | text | YES | コメント |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

#### notification_logs

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | YES |
| booking_id | bigint | FK→bookings | YES |
| channel | varchar | NO | email / line / chatwork / chatwork_system |
| type | varchar | NO | booking_confirmed / reminder_day_before 等 |
| subject | varchar | YES | 件名 |
| content | text | YES | 本文 |
| status | varchar | NO | sent / failed |
| error_message | text | YES | エラー詳細 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

#### audit_logs

| カラム | 型 | NULL | 説明 |
|--------|------|------|------|
| id | bigint | PK | |
| user_id | bigint | FK→users | YES |
| action | varchar | NO | 操作種別 |
| model_type | varchar | YES | 対象モデル |
| model_id | bigint | YES | 対象ID |
| old_values | json | YES | 変更前の値 |
| new_values | json | YES | 変更後の値 |
| ip_address | varchar | YES | IPアドレス |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | |

#### その他テーブル

| テーブル | 説明 |
|----------|------|
| favorites | お気に入り（user_id, consultant_id の pivot） |
| system_settings | キー・バリュー形式のシステム設定 |
| schedule_requests | ゲストからのスケジュール枠リクエスト |
| guest_message_templates | ゲスト向けメールテンプレート |
| cache | キャッシュ（Laravel標準） |
| jobs / job_batches / failed_jobs | キュー（Laravel標準） |
| sessions | セッション（Laravel標準） |
| password_reset_tokens | パスワードリセット |

---

## 3. URL設計・ルーティング

### 3.1 公開（認証不要）

| メソッド | URL | 機能 |
|----------|-----|------|
| GET/POST | `/consultation` | ゲスト向け空き日程一覧 |
| GET | `/consultation/book/{schedule}` | ゲスト予約フォーム |
| POST | `/consultation/book` | ゲスト予約作成 |
| GET | `/consultation/complete/{booking}` | 予約完了ページ |
| GET/POST | `/consultation/schedule-request` | スケジュールリクエスト |
| GET | `/consultation/schedule-request/complete` | リクエスト完了ページ |

### 3.2 認証

| メソッド | URL | 機能 |
|----------|-----|------|
| GET/POST | `/login` | ログイン |
| GET/POST | `/register` | 会員登録 |
| POST | `/logout` | ログアウト |
| GET/POST | `/forgot-password` | パスワードリセット要求 |
| GET/POST | `/reset-password/{token}` | パスワードリセット実行 |

### 3.3 会員（role:user）

| メソッド | URL | 機能 |
|----------|-----|------|
| GET | `/user/dashboard` | ダッシュボード |
| GET | `/user/schedules` | スケジュール閲覧 |
| GET | `/user/consultants` | コンサルタント一覧 |
| GET | `/user/consultants/{id}` | コンサルタント詳細 |
| GET | `/user/bookings` | 予約一覧 |
| GET/POST | `/user/bookings/create/{schedule}` | 予約作成 |
| GET | `/user/bookings/{booking}` | 予約詳細 |
| POST | `/user/bookings/{booking}/cancel` | 予約キャンセル |
| POST | `/user/bookings/{booking}/review` | レビュー投稿 |
| GET/POST | `/user/favorites/{consultant}` | お気に入り切替 |
| GET/PUT | `/user/profile` | プロフィール編集 |
| PUT | `/user/profile/password` | パスワード変更 |

### 3.4 コンサルタント（role:consultant）

| メソッド | URL | 機能 |
|----------|-----|------|
| GET | `/consultant/dashboard` | ダッシュボード |
| GET/POST | `/consultant/schedules` | スケジュール管理・作成 |
| POST | `/consultant/schedules/bulk` | 一括作成 |
| DELETE | `/consultant/schedules/{schedule}` | 枠削除 |
| GET | `/consultant/bookings` | 予約管理 |
| POST | `/consultant/bookings/{booking}/complete` | 完了処理 |
| POST | `/consultant/bookings/{booking}/cancel` | キャンセル |
| PUT | `/consultant/bookings/{booking}/consultation-record` | 相談記録更新 |
| PUT | `/consultant/bookings/{booking}/notes` | 内部メモ更新 |
| POST | `/consultant/bookings/{booking}/email` | メール送信 |
| GET/PUT | `/consultant/profile` | プロフィール編集 |
| PUT | `/consultant/profile/password` | パスワード変更 |
| DELETE | `/consultant/profile/photo` | 写真削除 |
| GET/POST | `/consultant/google/*` | Google カレンダー連携 |

### 3.5 管理者（role:admin）

| メソッド | URL | 機能 |
|----------|-----|------|
| GET | `/admin/dashboard` | ダッシュボード |
| GET/POST | `/admin/users` | ユーザー管理 |
| GET/POST | `/admin/users/create` | ユーザー作成 |
| GET/PUT | `/admin/users/{user}` | ユーザー編集 |
| POST | `/admin/users/{user}/toggle-active` | 有効/無効切替 |
| GET/PUT | `/admin/users/guest/{booking}` | ゲスト情報編集 |
| POST | `/admin/users/{user}/chatwork` | CW メッセージ送信 |
| GET | `/admin/bookings` | 予約一覧 |
| GET | `/admin/bookings/export-csv` | CSV ダウンロード |
| GET/POST | `/admin/bookings/create` | 代理予約作成 |
| POST | `/admin/bookings/{booking}/cancel` | キャンセル |
| PUT | `/admin/bookings/{booking}/consultation-record` | 相談記録更新 |
| POST | `/admin/bookings/{booking}/reset-consultation-record` | 相談記録リカバリー |
| PUT | `/admin/bookings/{booking}/notes` | 管理者メモ |
| POST | `/admin/bookings/{booking}/guest-email` | ゲストメール送信 |
| GET/PUT | `/admin/schedule-requests` | スケジュールリクエスト管理 |
| GET/POST | `/admin/schedules` | スケジュール代理作成 |
| GET | `/admin/stats` | コンサルタント統計 |
| GET | `/admin/stats/{consultant}` | 個人統計 |
| GET | `/admin/user-stats` | ユーザー統計 |
| GET/PUT | `/admin/settings` | システム設定 |
| GET/POST | `/admin/google/*` | Google カレンダー連携 |

---

## 4. サービスクラス仕様

### 4.1 NotificationService

予約に関する全通知を一元管理するサービス。

| メソッド | 引数 | 処理内容 |
|----------|------|----------|
| sendBookingConfirmation | Booking | 予約者・コンサルタント・システムCWに確認通知 |
| sendBookingCancelled | Booking | キャンセル通知を関係者に送信 |
| sendReminder | Booking, type | リマインダー送信（ゲスト/会員で分岐） |
| sendGuestReminder | Booking, type | ゲスト向けリマインダー |
| sendMorningChatworkNotification | Booking | 朝のChatwork通知 |
| sendConsultationRecordNotification | Booking | 相談記録入力の通知 |

**重複送信防止の仕組み:**

| 対策 | 説明 |
|------|------|
| NotificationLog チェック | 同一予約・同一チャネル・同一タイプの通知が直近1時間以内に送信済みの場合はスキップ |
| システムルーム重複防止 | コンサルタントの個人 Chatwork ルームとシステム通知ルームが同一の場合、システム通知をスキップ |
| 朝の通知フラグ | `morning_chatwork_sent` フラグにより、スケジューラーが複数回実行されても重複送信しない |
| リマインダーフラグ | `reminder_day_before_sent` / `reminder_day_of_sent` / `reminder_10min_sent` フラグで各リマインダーの送信を1回に制限 |
| 相談記録リセット後 | `consultation_record_reset_at` 以降の通知ログのみ参照し、リセット後の再入力を「初回」として通知 |

### 4.2 GoogleCalendarService

Google Calendar API との連携を管理するサービス。

| メソッド | 処理内容 |
|----------|----------|
| syncCreateEvent(Booking) | 管理者＋コンサルタント両カレンダーにイベント作成 |
| syncDeleteEvent(Booking) | 両カレンダーからイベント削除 |
| checkSlotConflict(...) | 指定カレンダーと時間枠の重複チェック |
| listCalendars(token) | カレンダー一覧取得 |
| listAllCalendars(token) | 全カレンダー一覧（委任含む） |

**イベント作成時の内容:**

| 項目 | 内容 |
|------|------|
| タイトル | 予約者名 + コンサルタント名 |
| 日時 | 予約の開始〜終了時間 |
| 出席者 | 予約者メール + コンサルタントメール |
| リマインダー | メール（1日前）+ ポップアップ（10分前） |
| 説明 | 予約タイプ、備考等 |

**エラー時の動作:** Google カレンダー連携はオプション扱い。API エラーが発生しても予約処理自体は継続する。

**同期先カレンダー変更時の挙動:**

| 操作 | 結果 |
|------|------|
| 旧カレンダーの既存イベント | そのまま残る（移動・削除されない） |
| 変更後の新規予約 | 新しいカレンダーにイベント作成 |
| 変更前の予約のキャンセル | 作成時のカレンダーIDが予約に記録されているため、正しいカレンダーからイベント削除 |

※ 予約作成時に使用したカレンダーID（管理者・コンサルタント両方）を `bookings` テーブルの `admin_google_calendar_id` / `consultant_google_calendar_id` に保存し、キャンセル時にそのIDを参照して削除する。

### 4.3 ChatworkService

| メソッド | 処理内容 |
|----------|----------|
| sendMessage(roomId, message) | 指定ルームにメッセージ送信 |
| getRoomMembers(roomId) | ルームメンバー一覧取得 |

---

## 5. バッチ処理

### 5.1 bookings:send-reminders

| 項目 | 値 |
|------|------|
| 実行間隔 | 毎分（cron） |
| 対象 | ステータスが approved の予約 |
| 処理内容 | 前日・当日・直前リマインダー、朝のChatwork通知 |
| 重複防止 | 各予約の送信済みフラグで制御 |

### 5.2 calendar:sync-conflicts

| 項目 | 値 |
|------|------|
| 実行間隔 | 10分毎 |
| 対象 | Google カレンダー連携済みの全コンサルタント |
| 処理内容 | 未来のスケジュール枠とカレンダー予定を照合 |
| 重複検出時 | 枠を自動ブロック（calendar_blocked = true） |
| 重複解消時 | ブロックを自動解除（calendar_blocked = false） |

---

## 6. 認証・認可

### 6.1 認証方式

- Laravel 標準のセッションベース認証
- パスワード: bcrypt ハッシュ
- パスワードリセット: メールトークン（60分有効）

### 6.2 認可（ロールベースアクセス制御）

| ミドルウェア | 適用パス | 許可ロール |
|-------------|---------|-----------|
| guest | `/login`, `/register` | 未認証のみ |
| auth, role:user | `/user/*` | user |
| auth, role:consultant | `/consultant/*` | consultant |
| auth, role:admin | `/admin/*` | admin |
| （なし） | `/consultation/*` | 全員（ゲスト含む） |

---

## 7. 通知仕様

### 7.1 チャネル

| チャネル | 識別子 | 設定 |
|----------|--------|------|
| メール | email | ユーザー.notify_email |
| LINE | line | ユーザー.notify_line + line_user_id |
| Chatwork（個人） | chatwork | ユーザー.notify_chatwork + chatwork_room_id |
| Chatwork（システム） | chatwork_system | システム設定.chatwork_enabled + chatwork_room_id |

### 7.2 通知タイプと送信先

| タイプ | 予約者 | コンサルタント | システムCW |
|--------|--------|---------------|-----------|
| booking_confirmed | Email/LINE/CW | Email/LINE/CW | CW |
| booking_cancelled | Email/LINE/CW | Email/LINE/CW | CW |
| reminder_day_before | Email/LINE/CW | Email/LINE/CW | - |
| reminder_day_of | Email/LINE/CW | Email/LINE/CW | - |
| reminder_10min | Email/LINE/CW | Email/LINE/CW | - |
| morning_chatwork | - | CW | - |
| consultation_record | - | - | CW |

### 7.3 Chatwork 連携仕様

**システム通知:** 予約確認・キャンセル時に、システム設定の `chatwork_room_id` で指定されたルームにメッセージが自動送信される。コンサルタントへのメンション（`[To:account_id]`）付き。

**ユーザー個別通知:** 各ユーザーに以下が設定されている場合、そのユーザーの Chatwork ルームに個別通知が送られる。

| ユーザー設定 | 説明 |
|-------------|------|
| `chatwork_id` | Chatwork のアカウント ID |
| `chatwork_room_id` | 通知先のルーム ID |
| `notify_chatwork` | 通知の ON/OFF |

### 7.4 LINE 連携仕様

以下の条件を全て満たす場合にプッシュメッセージを送信する。

| 条件 | 説明 |
|------|------|
| `line_user_id` | ユーザーに LINE ユーザーID が設定されている |
| `notify_line` | ユーザーの LINE 通知設定が ON |
| `line_enabled` | システム設定で LINE 通知が有効 |

### 7.5 テンプレートプレースホルダー

| プレースホルダー | 置換内容 |
|-----------------|----------|
| `{name}` | 予約者氏名 |
| `{date}` | 予約日時 |
| `{consultant}` | コンサルタント名 |
| `{meeting_url}` | ミーティングURL |
| `{chatwork_id}` | Chatwork ID |
| `{important_document_url}` | 重要事項説明書URL |

---

## 8. システム設定キー一覧

### 予約設定

| キー | デフォルト | 説明 |
|------|-----------|------|
| booking_acceptance_enabled | 1 | 予約受付 ON/OFF |
| schedule_disclosure_days | 30 | 公開日数 |
| hours_from_now | 2 | 最低予約時間（時間後） |
| max_bookings_per_day | 8 | 1日上限 |
| cancel_policy_hours | 24 | キャンセル期限（時間前） |

### リマインダー設定

| キー | 説明 |
|------|------|
| reminder_day_before_enabled / _hour | 前日リマインダー ON/OFF・時刻 |
| reminder_day_of_enabled / _hour | 当日リマインダー ON/OFF・時刻 |
| reminder_minutes_before_enabled / _minutes | 直前リマインダー ON/OFF・分数 |

### テンプレート設定

| キー | 説明 |
|------|------|
| `booking_confirm_email_subject` | 予約確認メール件名 |
| `booking_confirm_email_body` | 予約確認メール本文 |
| `booking_confirm_line_message` | 予約確認 LINE メッセージ |
| `cancel_notification_email_subject` | キャンセル通知メール件名 |
| `cancel_notification_email_body` | キャンセル通知メール本文 |
| `cancel_notification_line_message` | キャンセル通知 LINE メッセージ |
| `reminder_*_email_subject` | リマインダーメール件名 |
| `reminder_*_email_body` | リマインダーメール本文 |
| `reminder_*_line_message` | リマインダー LINE メッセージ |

### Chatwork テンプレート

| キー | 説明 |
|------|------|
| `chatwork_booking_confirm_message` | 予約確認 Chatwork メッセージ |
| `chatwork_cancel_notification_message` | キャンセル Chatwork メッセージ |
| `chatwork_morning_notification_message` | 朝の通知 Chatwork メッセージ |

### 外部連携設定

| キー | 説明 |
|------|------|
| `line_channel_token` | LINE チャネルトークン |
| `line_channel_secret` | LINE チャネルシークレット |
| `line_enabled` | LINE 通知の有効/無効 |
| `chatwork_api_token` | Chatwork API トークン |
| `chatwork_room_id` | Chatwork システム通知ルーム ID |
| `chatwork_enabled` | Chatwork 通知の有効/無効 |
| `google_calendar_enabled` | Google カレンダーの有効/無効 |
| `google_refresh_token` | 管理者 Google リフレッシュトークン |
| `google_calendar_id` | 管理者カレンダー ID |

---

## 9. ヘルパークラス・スコープ

### 9.1 JapaneseHolidays ヘルパー

スケジュール一括作成時の祝日スキップに使用する日本の祝日判定クラス。

| 対応祝日 |
|----------|
| 元日、成人の日、建国記念日、天皇誕生日 |
| 春分の日、秋分の日 |
| 昭和の日、憲法記念日、みどりの日、こどもの日 |
| 海の日、山の日、敬老の日、スポーツの日 |
| 文化の日、勤労感謝の日 |
| ハッピーマンデー、振替休日、国民の休日 |

### 9.2 ConsultantSchedule 主要スコープ

| スコープ | 説明 |
|----------|------|
| `upcoming()` | 現在以降のスケジュールのみ |
| `withinDailyLimit()` | 1日上限（`max_bookings_per_day`）に達していないスケジュールのみ |
| `acceptingBookings()` | 予約受付が ON（`booking_acceptance_enabled`）のコンサルタントのスケジュールのみ |
| `notCalendarBlocked()` | カレンダー重複でブロックされていないスケジュールのみ |

---

## 10. 予約ステータス遷移

```
作成 ──▶ [approved（承認済み）]
              │
              ├── キャンセル ──▶ [cancelled]
              │
              ▼
         [completed（完了）]
              │
              └── 管理者リカバリー ──▶ [approved に差し戻し]
                  （consultation_result, important_document_issued をクリア）
                  （consultation_notes は保持）
```
