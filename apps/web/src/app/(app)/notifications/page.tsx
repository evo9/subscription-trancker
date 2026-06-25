'use client';

import { useState } from 'react';
import { useNotifications, useMarkNotificationRead } from '@/lib/queries';
import type { AppNotification } from '@/types/api';

function NotificationItem({
  notification,
  onRead,
}: {
  notification: AppNotification;
  onRead: (id: string) => void;
}) {
  const isUnread = notification.read_at === null;
  const d = notification.data;

  return (
    <li
      className={`px-6 py-4 flex items-start justify-between gap-4 ${
        isUnread ? 'bg-zinc-50' : ''
      }`}
    >
      <div className='min-w-0'>
        <p className={`text-sm ${isUnread ? 'font-medium text-zinc-900' : 'text-zinc-600'}`}>
          <span className='font-semibold'>{d.subscription_name}</span> renews on{' '}
          {d.next_billing_date}
        </p>
        <p className='text-xs text-zinc-400 mt-0.5'>
          {d.amount} {d.currency}
          {notification.read_at
            ? ` · Read ${new Date(notification.read_at).toLocaleDateString()}`
            : ''}
        </p>
      </div>
      {isUnread && (
        <button
          onClick={() => onRead(notification.id)}
          className='flex-shrink-0 text-xs text-zinc-500 hover:text-zinc-900 underline transition-colors'
        >
          Mark read
        </button>
      )}
    </li>
  );
}

export default function NotificationsPage() {
  const { data: notifications, isLoading } = useNotifications();
  const markRead = useMarkNotificationRead();
  const [markError, setMarkError] = useState<string | null>(null);

  function handleMarkRead(id: string) {
    setMarkError(null);
    markRead.mutate(id, { onError: () => setMarkError('Failed to mark notification as read.') });
  }

  return (
    <div className='space-y-6'>
      <h1 className='text-2xl font-semibold text-zinc-900'>Notifications</h1>

      {markError && (
        <p className='text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2'>{markError}</p>
      )}

      <div className='rounded-xl border border-zinc-200 bg-white'>
        {isLoading ? (
          <div className='p-6 space-y-4'>
            {[0, 1, 2].map((i) => (
              <div key={i} className='h-5 bg-zinc-100 rounded animate-pulse' />
            ))}
          </div>
        ) : !notifications?.length ? (
          <p className='p-8 text-center text-sm text-zinc-400'>No notifications yet.</p>
        ) : (
          <ul className='divide-y divide-zinc-100'>
            {notifications.map((n) => (
              <NotificationItem
                key={n.id}
                notification={n}
                onRead={handleMarkRead}
              />
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
