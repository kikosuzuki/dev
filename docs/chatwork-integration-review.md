# Chatwork連携 現状棚卸しレポート

**作成日:** 2026-02-24
**対象ブランチ:** kiko-prj-booking-2023

---

## 1. 現在の実装概要

### アーキテクチャ

| レイヤー | ファイル | 役割 |
|---------|---------|------|
| Service | `app/Services/ChatworkService.php` | Chatwork API通信 |
| Service | `app/Services/NotificationService.php` | 通知ロジック統括 |
| Controller | `app/Http/Controllers/Admin/ChatworkController.php` | 管理画面からの手動送信 |
| Controller | `app/Http/Controllers/Api/ChatworkMemberController.php` | ルームメンバー取得API |
| Command | `app/Console/Commands/SendBookingReminders.php` | スケジュール実行（朝通知含む） |
| Model | `app/Models/NotificationLog.php` | 通知ログ記録 |

### システム通知（3種）

| 通知タイプ | 設定キー | トリガー |
|-----------|---------|---------|
| 予約確認 | `chatwork_booking_confirm_message` | 新規予約作成時 |
| キャンセル | `chatwork_cancel_notification_message` | 予約キャンセル時 |
| 朝通知 | `chatwork_morning_notification_message` | 毎朝8:00（スケジューラ） |

### 設定項目（SystemSetting）

| キー | 型 | 説明 |
|-----|---|------|
| `chatwork_enabled` | boolean | Chatwork連携の有効/無効 |
| `chatwork_api_token` | string | APIトークン |
| `chatwork_room_id` | string (max 50) | システム通知先ルームID |
| `chatwork_booking_confirm_message` | string (max 2000) | 予約確認メッセージテンプレート |
| `chatwork_cancel_notification_message` | string (max 2000) | キャンセルメッセージテンプレート |
| `chatwork_morning_notification_message` | string (max 2000) | 朝通知メッセージテンプレート |

### メッセージプレースホルダー

| プレースホルダー | 置換内容 |
|----------------|---------|
| `{name}` | 予約者名 |
| `{date}` | 予約日時 |
| `{consultant}` | コンサルタント名 |
| `{meeting_url}` | ミーティングURL |
| `{chatwork_id}` | コンサルタントのChatworkアカウントID |

### DB上のChatwork関連フィールド

| テーブル | フィールド | 用途 |
|---------|-----------|------|
| `users` | `chatwork_id` | ユーザーのChatworkアカウントID（TO指定用） |
| `users` | `chatwork_room_id` | ユーザー個別の通知先ルームID |
| `consultant_profiles` | `chatwork_account_id` | コンサルタントのChatworkアカウントID（TO指定用） |

### 通知チャネル（NotificationLog）

| チャネル | 説明 |
|---------|------|
| `chatwork` | ユーザー個別ルームへの通知 |
| `chatwork_system` | システムルームへの通知 |
| `email` | メール通知 |
| `line` | LINE通知 |

### 現在の通知フロー

```
予約確認 ──→ ユーザー通知 (chatwork_room_id宛)   ← chatwork_enabledチェック無し
         ├→ システム通知 (system room_id宛)       ← chatwork_enabledチェック有り
         └→ メール/LINE                           ← notify_email/notify_lineチェック有り

キャンセル → 同上

朝8時通知 → システム通知のみ（重複送信防止なし）

リマインダー → ユーザー通知のみ（システム通知なし）
```

---

## 2. 検出された問題

### 2.1 重大 (CRITICAL)

#### `guest_line_user_id` がDBに存在しない

- **場所:** `NotificationService.php` (複数箇所)
- **内容:** `$booking->guest_line_user_id` を参照しているが、`bookings`テーブルにこのカラムが存在しない。マイグレーションにもモデルのfillableにも未定義。
- **影響:** ゲスト予約のLINE通知が常にスキップされる（サイレント失敗）。

### 2.2 高 (HIGH)

#### Chatwork通知のON/OFFフラグが存在しない

- **場所:** `NotificationService.php:344-346`
- **内容:** メールは`notify_email`、LINEは`notify_line`で制御されるが、Chatworkは`chatwork_room_id`があれば常に送信される。`notify_chatwork`のような制御フラグが存在しない。
- **影響:** ユーザーがChatwork通知を止めたい場合、room_idを削除するしか方法がない。

#### 朝通知の重複送信防止がない

- **場所:** `SendBookingReminders.php:64-79`
- **内容:** 他のリマインダーには`reminder_day_before_sent`等のフラグがあるが、朝のChatwork通知にはフラグがない。同じ時間帯にコマンドが複数回実行されると重複送信される。
- **影響:** 同じ予約に対して同じ通知が複数回送信される可能性がある。

### 2.3 中 (MEDIUM)

#### APIトークンの平文保存

- **場所:** SystemSettingテーブル
- **内容:** `chatwork_api_token`が暗号化されずにDBに保存されている。管理画面のフォームでもvalue属性に平文表示。
- **推奨:** Laravelの`Crypt`ファサードで暗号化保存し、フォームでは`••••••••`表示にする。

#### API呼び出しのリトライ機構がない

- **場所:** `ChatworkService.php`
- **内容:** ネットワークエラーや429（レート制限）で即座に失敗し、リトライしない。
- **推奨:** 指数バックオフ付きリトライ（最大3回程度）を実装する。

#### ChatworkServiceの毎回インスタンス生成

- **場所:** `NotificationService.php` (380行目、434行目)
- **内容:** `new ChatworkService()` が複数箇所で呼ばれ、その都度DBからトークンを取得している。
- **推奨:** DIコンテナでシングルトン登録するか、メソッドインジェクションを使用する。

#### 朝通知の配信時刻がハードコード

- **場所:** `SendBookingReminders.php:64`
- **内容:** `$now->hour === 8` で固定されており、管理画面から変更できない。
- **推奨:** `chatwork_morning_notification_hour` のような設定項目を追加する。

#### chatwork_enabledチェックの不統一

- **場所:** `NotificationService.php`
- **内容:** システム通知は`chatwork_enabled`をチェックするが、ユーザー個別通知はチェックしない。グローバル無効にしてもユーザー個別通知は送信され続ける。

#### 管理画面手動送信でAPIトークン未設定チェックがない

- **場所:** `ChatworkController.php`
- **内容:** APIトークンが未設定でもメッセージ送信を試行し、エラーになる。事前チェックがない。

### 2.4 低 (LOW)

| 問題 | 詳細 |
|------|------|
| Room ID/Account IDのフォーマット未検証 | 数値チェックがなく、任意の文字列を受け入れる |
| APIエンドポイントにレート制限なし | `/api/chatwork/members/{roomId}` |
| NotificationLogテーブルのインデックス不足 | `booking_id`, `user_id`, `status` 等 |
| 接続テスト機能がない | APIトークン/ルームIDの正当性を確認する手段がない |
| Chatwork通知履歴の閲覧UIがない | 送信成功/失敗を管理画面で確認できない |

---

## 3. 命名の不整合

| フィールド | テーブル | 用途 | 問題 |
|-----------|---------|------|------|
| `chatwork_id` | users | CWアカウントID (TO指定) | `chatwork_account_id`と同じ目的だが異なる名前 |
| `chatwork_room_id` | users | 個別通知先ルームID | OK |
| `chatwork_account_id` | consultant_profiles | CWアカウントID (TO指定) | `chatwork_id`と同じ目的だが異なる名前 |

---

## 4. 推奨対応の優先順位

### P0: 即時対応

1. `bookings`テーブルに`guest_line_user_id`カラムを追加するマイグレーション作成
2. 朝通知の重複送信防止フラグ追加（`morning_chatwork_sent`等）
3. `chatwork_enabled`チェックをユーザー通知にも適用

### P1: 短期対応

4. `notify_chatwork`フラグをusersテーブルに追加
5. APIトークンの暗号化保存
6. ChatworkServiceのDI化
7. API呼び出しのリトライ機構追加
8. 管理画面手動送信の事前チェック強化

### P2: 中期対応

9. 朝通知の配信時刻を設定可能にする
10. Room ID / Account IDのバリデーション強化
11. NotificationLogのインデックス追加
12. 接続テスト機能の実装
13. 通知履歴閲覧UI

### P3: 改善

14. フィールド命名の統一（`chatwork_id` → `chatwork_account_id`）
15. APIエンドポイントのレート制限
16. 通知の再送機能
