'use client';

import { useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  useSubscription,
  useSubscriptionPayments,
  usePauseSubscription,
  useResumeSubscription,
  useDeleteSubscription,
} from '@/lib/queries';

function Field({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <dt className='text-xs text-zinc-500'>{label}</dt>
      <dd className='text-sm font-medium text-zinc-900 mt-0.5'>{value}</dd>
    </div>
  );
}

export default function SubscriptionDetailPage() {
  const { id } = useParams<{ id: string }>();
  const subId = Number(id);
  const router = useRouter();
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  const { data: sub, isLoading } = useSubscription(subId);
  const { data: payments, isLoading: paymentsLoading } = useSubscriptionPayments(subId);
  const pause = usePauseSubscription();
  const resume = useResumeSubscription();
  const remove = useDeleteSubscription();

  async function handleDelete() {
    setDeleteError(null);
    try {
      await remove.mutateAsync(subId);
      router.push('/subscriptions');
    } catch {
      setDeleteError('Failed to delete subscription. Please try again.');
      setConfirmDelete(false);
    }
  }

  if (isLoading) {
    return (
      <div className='space-y-6'>
        <div className='h-8 w-64 bg-zinc-100 rounded animate-pulse' />
        <div className='rounded-xl border border-zinc-200 bg-white p-6 space-y-4'>
          {[0, 1, 2, 3].map((i) => (
            <div key={i} className='h-5 bg-zinc-100 rounded animate-pulse' />
          ))}
        </div>
      </div>
    );
  }

  if (!sub) return <p className='text-sm text-zinc-400'>Subscription not found.</p>;

  const isActive = sub.status === 'active';
  const isPaused = sub.status === 'paused';

  return (
    <div className='space-y-6 max-w-2xl'>
      <div className='flex items-start justify-between gap-4'>
        <div>
          <h1 className='text-2xl font-semibold text-zinc-900'>{sub.name}</h1>
          {sub.description && (
            <p className='text-sm text-zinc-500 mt-1'>{sub.description}</p>
          )}
        </div>
        <Link
          href={`/subscriptions/${subId}/edit`}
          className='flex-shrink-0 rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors'
        >
          Edit
        </Link>
      </div>

      <div className='rounded-xl border border-zinc-200 bg-white p-6'>
        <dl className='grid grid-cols-2 gap-x-6 gap-y-4'>
          <Field label='Status' value={sub.status} />
          <Field label='Billing cycle' value={sub.billing_cycle} />
          <Field label='Price' value={`${sub.price} ${sub.currency}`} />
          <Field label='Monthly cost' value={`$${sub.monthly_cost.toFixed(2)}`} />
          <Field label='Yearly cost' value={`$${sub.yearly_cost.toFixed(2)}`} />
          <Field label='Category' value={sub.category?.name ?? '—'} />
          <Field label='Started' value={sub.started_at} />
          <Field label='Next billing' value={sub.next_billing_date} />
          <Field label='Remind' value={`${sub.notify_days_before} day(s) before`} />
        </dl>
      </div>

      {deleteError && (
        <p className='text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2'>{deleteError}</p>
      )}

      <div className='flex gap-3'>
        {isActive && (
          <button
            onClick={() => pause.mutate(subId)}
            disabled={pause.isPending}
            className='rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-50 transition-colors'
          >
            {pause.isPending ? 'Pausing…' : 'Pause'}
          </button>
        )}
        {isPaused && (
          <button
            onClick={() => resume.mutate(subId)}
            disabled={resume.isPending}
            className='rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-50 transition-colors'
          >
            {resume.isPending ? 'Resuming…' : 'Resume'}
          </button>
        )}
        {!confirmDelete ? (
          <button
            onClick={() => setConfirmDelete(true)}
            className='rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors'
          >
            Delete
          </button>
        ) : (
          <div className='flex items-center gap-2'>
            <span className='text-sm text-zinc-600'>Delete this subscription?</span>
            <button
              onClick={handleDelete}
              disabled={remove.isPending}
              className='rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 transition-colors'
            >
              {remove.isPending ? 'Deleting…' : 'Confirm'}
            </button>
            <button
              onClick={() => setConfirmDelete(false)}
              className='rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors'
            >
              Cancel
            </button>
          </div>
        )}
      </div>

      <div className='rounded-xl border border-zinc-200 bg-white'>
        <div className='px-6 py-4 border-b border-zinc-100'>
          <h2 className='text-sm font-medium text-zinc-700'>Payment history</h2>
        </div>
        {paymentsLoading ? (
          <div className='p-6 space-y-3'>
            {[0, 1, 2].map((i) => (
              <div key={i} className='h-5 bg-zinc-100 rounded animate-pulse' />
            ))}
          </div>
        ) : !payments?.length ? (
          <p className='p-6 text-sm text-zinc-400'>No payments recorded yet.</p>
        ) : (
          <ul className='divide-y divide-zinc-100'>
            {payments.map((p) => (
              <li key={p.id} className='px-6 py-3 flex items-center justify-between'>
                <span className='text-sm text-zinc-600'>{p.paid_at}</span>
                <span className='text-sm font-medium text-zinc-900'>
                  {p.amount} {p.currency}
                </span>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
