# Xserver本番環境 cron設定・マイグレーション手順

## 1. cron設定（最重要）

Xserverサーバーパネル → cron設定で以下を追加：

```
* * * * * cd /home/ctwasia2/ycscampaign.com/consul-app && php artisan schedule:run >> /dev/null 2>&1
```

これにより以下のバッチが自動実行されます：

| コマンド | 実行間隔 | 説明 |
|---------|---------|------|
| `bookings:send-reminders` | 毎分 | 予約リマインド通知（前日・当日・開始前）＋朝Chatwork通知 |
| `calendar:sync-conflicts` | 10分毎 | Googleカレンダー重複チェック・自動ブロック・自動復活 |

## 2. マイグレーション実行

デプロイ後に以下を実行：

```bash
cd /home/ctwasia2/ycscampaign.com/consul-app
php artisan migrate --force
```

### 今回追加されるマイグレーション

`consultant_schedules` テーブルに以下のカラムが追加されます：

| カラム | 型 | 説明 |
|-------|---|------|
| `calendar_blocked` | boolean (default: false) | Googleカレンダー重複によるブロック状態 |
| `calendar_blocked_reason` | string (nullable) | ブロック理由（カレンダーイベントのタイトル） |

## 3. 動作確認

### cron動作確認

```bash
# スケジューラが正しく動作するか確認
php artisan schedule:list

# calendar:sync-conflictsを手動実行してテスト
php artisan calendar:sync-conflicts
```

### 期待される動作

1. **自動ブロック**: Googleカレンダーに予定がある時間帯の予約枠が自動的にブロックされる
2. **自動復活**: Googleカレンダーから予定が削除された場合、ブロックが自動解除される
3. **表示**: コンサルタントのスケジュール画面でブロック中の枠が黄色で「GCal重複」と表示される
4. **予約不可**: ブロック中の枠はユーザー・ゲストの予約一覧に表示されない

### トラブルシューティング

- **バッチが動かない**: cronが設定されているか確認。`php artisan schedule:list` で登録済みタスクを確認
- **重複チェックされない**: コンサルタントがGoogleカレンダーを連携しているか確認（プロフィール設定 → Google連携）
- **APIエラー**: `storage/logs/laravel.log` でエラーログを確認。Google OAuth認証が有効か確認
