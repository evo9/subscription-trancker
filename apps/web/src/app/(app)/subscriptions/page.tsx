'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useSubscriptions, useCategories } from '@/lib/queries';
import type { SubscriptionStatus } from '@/types/api';

const STATUS_LABELS: Record<string, string> = {
  '': 'All statuses',
  active: 'Active',
  paused: 'Paused',
  cancelled: 'Cancelled',
};

const DUE_OPTIONS = [
  { value: '', label: 'Any date' },
  { value: '7', label: 'Next 7 days' },
  { value: '14', label: 'Next 14 days' },
  { value: '30', label: 'Next 30 days' },
];

const SELECT_CLS =
  'rounded-lg border border-zinc-300 px-3 py-1.5 text-sm text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-zinc-500';

export default function SubscriptionsPage() {
  const [status, setStatus] = useState<SubscriptionStatus | ''>('');
  const [categoryId, setCategoryId] = useState<number | undefined>();
  const [dueWithin, setDueWithin] = useState<number | undefined>();

  const { data: subscriptions, isLoading } = useSubscriptions({
    status: status || undefined,
    category_id: categoryId,
    due_within: dueWithin,
  });
  const { data: categories } = useCategories();

  return (
    <div className='space-y-6'>
      <div className='flex items-center justify-between'>
        <h1 className='text-2xl font-semibold text-zinc-900'>Subscriptions</h1>
        <Link
          href='/subscriptions/new'
          className='rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 transition-colors'
        >
          Add subscription
        </Link>
      </div>

      <div className='flex flex-wrap gap-3'>
        <select
          value={status}
          onChange={(e) => setStatus(e.target.value as SubscriptionStatus | '')}
          className={SELECT_CLS}
        >
          {Object.entries(STATUS_LABELS).map(([v, l]) => (
            <option key={v} value={v}>
              {l}
            </option>
          ))}
        </select>

        <select
          value={categoryId ?? ''}
          onChange={(e) =>
            setCategoryId(e.target.value ? Number(e.target.value) : undefined)
          }
          className={SELECT_CLS}
        >
          <option value=''>All categories</option>
          {categories?.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>

        <select
          value={dueWithin ?? ''}
          onChange={(e) =>
            setDueWithin(e.target.value ? Number(e.target.value) : undefined)
          }
          className={SELECT_CLS}
        >
          {DUE_OPTIONS.map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
      </div>

      <div className='rounded-xl border border-zinc-200 bg-white'>
        {isLoading ? (
          <div className='p-6 space-y-4'>
            {[0, 1, 2, 3, 4].map((i) => (
              <div key={i} className='h-5 bg-zinc-100 rounded animate-pulse' />
            ))}
          </div>
        ) : !subscriptions?.length ? (
          <p className='p-8 text-center text-sm text-zinc-400'>No subscriptions found.</p>
        ) : (
          <ul className='divide-y divide-zinc-100'>
            {subscriptions.map((sub) => (
              <li key={sub.id} className='px-6 py-4 flex items-center justify-between'>
                <div className='flex items-center gap-3 min-w-0'>
                  <span
                    className='h-2.5 w-2.5 rounded-full flex-shrink-0'
                    style={{
                      backgroundColor: sub.category?.color ?? '#a1a1aa',
                    }}
                  />
                  <div className='min-w-0'>
                    <Link
                      href={`/subscriptions/${sub.id}`}
                      className='text-sm font-medium text-zinc-900 hover:underline truncate block'
                    >
                      {sub.name}
                    </Link>
                    <p className='text-xs text-zinc-400 mt-0.5 capitalize'>
                      {sub.billing_cycle} · {sub.status}
                      {sub.category ? ` · ${sub.category.name}` : ''}
                    </p>
                  </div>
                </div>
                <div className='text-right flex-shrink-0 ml-4'>
                  <p className='text-sm font-medium text-zinc-900'>
                    {sub.price} {sub.currency}
                  </p>
                  <p className='text-xs text-zinc-400'>${sub.monthly_cost.toFixed(2)}/mo</p>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
