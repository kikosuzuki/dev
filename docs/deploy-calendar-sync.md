# カレンダー重複チェック機能 - 本番環境セットアップ依頼

## 対象PR
- PR #24: Googleカレンダー複数重複チェック・自動ブロック・自動復活機能

## 背景
PR #24で追加した「カレンダー重複チェックバッチ」が本番環境で動作していません。
以下の設定が必要です。

---

## 作業1: マイグレーション実行確認

```bash
cd /home/ctwasia2/ycscampaign.com/consul-app
php artisan migrate:status
```

以下2つが `Ran` になっていることを確認してください：
- `2026_03_10_100000_add_google_conflict_calendar_ids_to_consultant_profiles`
- `2026_03_10_100001_add_calendar_blocked_to_consultant_schedules`

**未実行の場合:**
```bash
php artisan migrate --force
```

---

## 作業2: cron設定（最重要）

Laravelスケジューラーを動かすために、cron設定が必要です。

### Xserverサーバーパネルから設定する場合
1. サーバーパネル → 「cron設定」
2. 以下を追加:
   - **分**: `*`（毎分）
   - **時**: `*`
   - **日**: `*`
   - **月**: `*`
   - **曜日**: `*`
   - **コマンド**: `cd /home/ctwasia2/ycscampaign.com/consul-app && php artisan schedule:run >> /dev/null 2>&1`

### SSHから設定する場合
```bash
crontab -e
```
以下の行を追加：
```
* * * * * cd /home/ctwasia2/ycscampaign.com/consul-app && php artisan schedule:run >> /dev/null 2>&1
```

### 補足
このcron設定で以下のバッチが自動実行されるようになります：
| コマンド | 間隔 | 内容 |
|---------|------|------|
| `bookings:send-reminders` | 毎分 | 予約リマインダー通知 |
| `calendar:sync-conflicts` | 10分毎 | Googleカレンダー重複チェック・自動ブロック/復活 |

> **注意**: cronが未設定の場合、既存の `bookings:send-reminders`（予約リマインダー）も動いていない可能性があります。

---

## 作業3: 動作確認

### バッチ手動実行
```bash
cd /home/ctwasia2/ycscampaign.com/consul-app
php artisan calendar:sync-conflicts -v
```

期待される出力例：
```
Processing consultant: suzuki+1@cwa-gws.com (2 conflict calendars)
  Checking 5 schedules...
  Blocked: 1, Unblocked: 0
Calendar sync completed.
```

### 画面確認
1. コンサルタント（suzuki+1@cwa-gws.com）でログイン
2. スケジュール管理 → リスト表示
3. Googleカレンダーに予定がある時間帯の枠に黄色の「カレンダー重複」バッジが表示されればOK

---

## エラーが出た場合

| エラー内容 | 対処 |
|-----------|------|
| `invalid_grant` / token expired | コンサルタントにGoogleカレンダーの再連携を依頼 |
| `403 Forbidden` | カレンダーへのアクセス権限を確認 |
| `404 Not Found` | 設定されたカレンダーIDが存在しない可能性 |
| `cURL error` | Xserverからの外部API接続を確認 |

エラー出力をそのまま共有いただければ対処します。
