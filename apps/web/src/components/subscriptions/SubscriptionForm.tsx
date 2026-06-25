'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import axios from 'axios';
import { useCategories } from '@/lib/queries';
import type { ApiError, BillingCycle, SubscriptionFormValues } from '@/types/api';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  description: z.string().optional(),
  category_id: z.string().optional(),
  price: z
    .string()
    .regex(/^\d+(\.\d{1,2})?$/, 'Enter a valid price (e.g. 9.99)'),
  currency: z.string().length(3, 'Must be exactly 3 characters'),
  billing_cycle: z.enum(['weekly', 'monthly', 'quarterly', 'yearly'] as const),
  started_at: z.string().min(1, 'Start date is required'),
  next_billing_date: z.string().min(1, 'Next billing date is required'),
  notify_days_before: z.number().int().min(0, 'Min 0 days').max(30, 'Max 30 days'),
});

type RawValues = z.infer<typeof schema>;

interface Props {
  defaultValues?: Partial<SubscriptionFormValues>;
  onSubmit: (values: SubscriptionFormValues) => Promise<void>;
  submitLabel?: string;
}

const INPUT_CLS =
  'w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500';
const LABEL_CLS = 'block text-sm font-medium text-zinc-700 mb-1';
const ERROR_CLS = 'mt-1 text-xs text-red-500';

export default function SubscriptionForm({ defaultValues, onSubmit, submitLabel = 'Save' }: Props) {
  const [serverError, setServerError] = useState<string | null>(null);
  const { data: categories } = useCategories();

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<RawValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: defaultValues?.name ?? '',
      description: defaultValues?.description ?? '',
      category_id: defaultValues?.category_id != null ? String(defaultValues.category_id) : '',
      price: defaultValues?.price ?? '',
      currency: defaultValues?.currency ?? 'USD',
      billing_cycle: defaultValues?.billing_cycle ?? 'monthly',
      started_at: defaultValues?.started_at ?? '',
      next_billing_date: defaultValues?.next_billing_date ?? '',
      notify_days_before: defaultValues?.notify_days_before ?? 3,
    },
  });

  async function submit(raw: RawValues) {
    setServerError(null);
    try {
      await onSubmit({
        name: raw.name,
        description: raw.description || null,
        category_id: raw.category_id ? Number(raw.category_id) : null,
        price: raw.price,
        currency: raw.currency.toUpperCase(),
        billing_cycle: raw.billing_cycle as BillingCycle,
        started_at: raw.started_at,
        next_billing_date: raw.next_billing_date,
        notify_days_before: raw.notify_days_before,
      });
    } catch (err) {
      if (axios.isAxiosError(err)) {
        const body = err.response?.data as ApiError | undefined;
        setServerError(body?.message ?? 'Something went wrong');
      } else {
        setServerError('Something went wrong');
      }
    }
  }

  return (
    <form onSubmit={handleSubmit(submit)} className='space-y-4'>
      <div>
        <label className={LABEL_CLS}>Name</label>
        <input {...register('name')} type='text' className={INPUT_CLS} />
        {errors.name && <p className={ERROR_CLS}>{errors.name.message}</p>}
      </div>

      <div>
        <label className={LABEL_CLS}>Description</label>
        <textarea
          {...register('description')}
          rows={2}
          className={INPUT_CLS}
        />
      </div>

      <div className='grid grid-cols-2 gap-4'>
        <div>
          <label className={LABEL_CLS}>Price</label>
          <input {...register('price')} type='text' inputMode='decimal' placeholder='9.99' className={INPUT_CLS} />
          {errors.price && <p className={ERROR_CLS}>{errors.price.message}</p>}
        </div>
        <div>
          <label className={LABEL_CLS}>Currency</label>
          <input {...register('currency')} type='text' maxLength={3} placeholder='USD' className={INPUT_CLS} />
          {errors.currency && <p className={ERROR_CLS}>{errors.currency.message}</p>}
        </div>
      </div>

      <div>
        <label className={LABEL_CLS}>Billing cycle</label>
        <select {...register('billing_cycle')} className={INPUT_CLS + ' bg-white'}>
          <option value='weekly'>Weekly</option>
          <option value='monthly'>Monthly</option>
          <option value='quarterly'>Quarterly</option>
          <option value='yearly'>Yearly</option>
        </select>
      </div>

      <div>
        <label className={LABEL_CLS}>Category</label>
        <select {...register('category_id')} className={INPUT_CLS + ' bg-white'}>
          <option value=''>No category</option>
          {categories?.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
      </div>

      <div className='grid grid-cols-2 gap-4'>
        <div>
          <label className={LABEL_CLS}>Start date</label>
          <input {...register('started_at')} type='date' className={INPUT_CLS} />
          {errors.started_at && <p className={ERROR_CLS}>{errors.started_at.message}</p>}
        </div>
        <div>
          <label className={LABEL_CLS}>Next billing date</label>
          <input {...register('next_billing_date')} type='date' className={INPUT_CLS} />
          {errors.next_billing_date && (
            <p className={ERROR_CLS}>{errors.next_billing_date.message}</p>
          )}
        </div>
      </div>

      <div>
        <label className={LABEL_CLS}>Remind me (days before)</label>
        <input
          {...register('notify_days_before', { valueAsNumber: true })}
          type='number'
          min={0}
          max={30}
          className={INPUT_CLS}
        />
        {errors.notify_days_before && (
          <p className={ERROR_CLS}>{errors.notify_days_before.message}</p>
        )}
      </div>

      {serverError && (
        <p className='text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2'>{serverError}</p>
      )}

      <button
        type='submit'
        disabled={isSubmitting}
        className='w-full rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 disabled:opacity-50 transition-colors'
      >
        {isSubmitting ? 'Saving…' : submitLabel}
      </button>
    </form>
  );
}
