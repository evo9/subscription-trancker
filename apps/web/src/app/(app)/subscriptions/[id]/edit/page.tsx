'use client';

import { useParams, useRouter } from 'next/navigation';
import { useSubscription, useUpdateSubscription } from '@/lib/queries';
import SubscriptionForm from '@/components/subscriptions/SubscriptionForm';
import type { SubscriptionFormValues } from '@/types/api';

export default function EditSubscriptionPage() {
  const { id } = useParams<{ id: string }>();
  const subId = Number(id);
  const router = useRouter();

  const { data: sub, isLoading } = useSubscription(subId);
  const update = useUpdateSubscription(subId);

  async function handleSubmit(values: SubscriptionFormValues) {
    await update.mutateAsync(values);
    router.push(`/subscriptions/${subId}`);
  }

  if (isLoading) {
    return (
      <div className='max-w-lg'>
        <div className='h-8 w-48 bg-zinc-100 rounded animate-pulse mb-8' />
        <div className='rounded-xl border border-zinc-200 bg-white p-6 space-y-4'>
          {[0, 1, 2, 3].map((i) => (
            <div key={i} className='h-9 bg-zinc-100 rounded animate-pulse' />
          ))}
        </div>
      </div>
    );
  }

  if (!sub) return <p className='text-sm text-zinc-400'>Subscription not found.</p>;

  return (
    <div className='max-w-lg'>
      <h1 className='text-2xl font-semibold text-zinc-900 mb-8'>Edit subscription</h1>
      <div className='rounded-xl border border-zinc-200 bg-white p-6'>
        <SubscriptionForm
          defaultValues={{
            name: sub.name,
            description: sub.description ?? '',
            category_id: sub.category?.id ?? null,
            price: sub.price,
            currency: sub.currency,
            billing_cycle: sub.billing_cycle,
            started_at: sub.started_at,
            next_billing_date: sub.next_billing_date,
            notify_days_before: sub.notify_days_before,
          }}
          onSubmit={handleSubmit}
          submitLabel='Save changes'
        />
      </div>
    </div>
  );
}
