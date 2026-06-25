'use client';

import Link from 'next/link';
import { useStatsSummary, useUpcomingRenewals } from '@/lib/queries';
import CategoryDonut from '@/components/charts/CategoryDonut';

function Skeleton({ className }: { className?: string }) {
  return <div className={`bg-zinc-100 rounded animate-pulse ${className ?? ''}`} />;
}

export default function DashboardPage() {
  const { data: summary, isLoading: summaryLoading } = useStatsSummary();
  const { data: upcoming, isLoading: upcomingLoading } = useUpcomingRenewals();

  return (
    <div className='space-y-8'>
      <h1 className='text-2xl font-semibold text-zinc-900'>Dashboard</h1>

      <div className='grid grid-cols-2 gap-4'>
        <div className='rounded-xl border border-zinc-200 bg-white p-6'>
          <p className='text-sm text-zinc-500'>Monthly</p>
          {summaryLoading ? (
            <Skeleton className='h-8 w-28 mt-1' />
          ) : (
            <p className='text-2xl font-semibold text-zinc-900 mt-1'>
              ${(summary?.monthly_total ?? 0).toFixed(2)}
            </p>
          )}
        </div>
        <div className='rounded-xl border border-zinc-200 bg-white p-6'>
          <p className='text-sm text-zinc-500'>Yearly</p>
          {summaryLoading ? (
            <Skeleton className='h-8 w-28 mt-1' />
          ) : (
            <p className='text-2xl font-semibold text-zinc-900 mt-1'>
              ${(summary?.yearly_total ?? 0).toFixed(2)}
            </p>
          )}
        </div>
      </div>

      <div className='rounded-xl border border-zinc-200 bg-white p-6'>
        <h2 className='text-sm font-medium text-zinc-700 mb-4'>Spending by category</h2>
        {summaryLoading ? (
          <Skeleton className='h-[220px]' />
        ) : (
          <>
            <CategoryDonut data={summary?.by_category ?? []} />
            {summary != null && summary.by_category.length > 0 && (
              <ul className='mt-4 space-y-1.5'>
                {summary.by_category.map((b, i) => (
                  <li key={i} className='flex items-center gap-2 text-sm text-zinc-600'>
                    <span
                      className='h-2.5 w-2.5 rounded-full flex-shrink-0'
                      style={{ backgroundColor: b.color ?? '#a1a1aa' }}
                    />
                    <span>{b.name ?? 'Uncategorized'}</span>
                    <span className='ml-auto text-zinc-900 font-medium'>
                      ${b.monthly_total.toFixed(2)}/mo
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </>
        )}
      </div>

      <div className='rounded-xl border border-zinc-200 bg-white'>
        <div className='px-6 py-4 border-b border-zinc-100'>
          <h2 className='text-sm font-medium text-zinc-700'>Upcoming renewals (30 days)</h2>
        </div>
        {upcomingLoading ? (
          <div className='p-6 space-y-3'>
            {[0, 1, 2].map((i) => (
              <Skeleton key={i} className='h-5' />
            ))}
          </div>
        ) : !upcoming?.length ? (
          <p className='p-6 text-sm text-zinc-400'>No upcoming renewals in the next 30 days.</p>
        ) : (
          <ul className='divide-y divide-zinc-100'>
            {upcoming.map((sub) => (
              <li key={sub.id} className='px-6 py-3 flex items-center justify-between'>
                <div>
                  <Link
                    href={`/subscriptions/${sub.id}`}
                    className='text-sm font-medium text-zinc-900 hover:underline'
                  >
                    {sub.name}
                  </Link>
                  <p className='text-xs text-zinc-400 mt-0.5'>{sub.next_billing_date}</p>
                </div>
                <span className='text-sm font-medium text-zinc-700'>
                  {sub.price} {sub.currency}
                </span>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
