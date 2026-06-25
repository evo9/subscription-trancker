'use client';

import { useRouter } from 'next/navigation';
import { useCreateSubscription } from '@/lib/queries';
import SubscriptionForm from '@/components/subscriptions/SubscriptionForm';
import type { SubscriptionFormValues } from '@/types/api';

export default function NewSubscriptionPage() {
  const router = useRouter();
  const create = useCreateSubscription();

  async function handleSubmit(values: SubscriptionFormValues) {
    await create.mutateAsync(values);
    router.push('/subscriptions');
  }

  return (
    <div className='max-w-lg'>
      <h1 className='text-2xl font-semibold text-zinc-900 mb-8'>Add subscription</h1>
      <div className='rounded-xl border border-zinc-200 bg-white p-6'>
        <SubscriptionForm onSubmit={handleSubmit} submitLabel='Add subscription' />
      </div>
    </div>
  );
}
