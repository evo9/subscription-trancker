'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import axios from 'axios';
import api from '@/lib/api';
import type { AuthResponse, ApiError } from '@/types/api';

const schema = z
  .object({
    name: z.string().min(1, 'Name is required'),
    email: z.string().email('Invalid email'),
    password: z.string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  });

type FormData = z.infer<typeof schema>;

export default function RegisterPage() {
  const router = useRouter();
  const [serverError, setServerError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  async function onSubmit(data: FormData) {
    setServerError(null);
    try {
      const response = await api.post<AuthResponse>('/api/register', data);
      localStorage.setItem('token', response.data.token);
      router.push('/dashboard');
    } catch (err) {
      if (axios.isAxiosError(err)) {
        const body = err.response?.data as ApiError | undefined;
        if (body?.errors) {
          const first = Object.values(body.errors)[0]?.[0];
          setServerError(first ?? body.message);
        } else {
          setServerError(body?.message ?? 'Registration failed');
        }
      } else {
        setServerError('An unexpected error occurred');
      }
    }
  }

  return (
    <>
      <h1 className='text-xl font-semibold text-zinc-900 mb-6'>
        Create account
      </h1>

      <form onSubmit={handleSubmit(onSubmit)} className='space-y-4'>
        <div>
          <label className='block text-sm font-medium text-zinc-700 mb-1'>
            Name
          </label>
          <input
            {...register('name')}
            type='text'
            autoComplete='name'
            className='w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-500'
          />
          {errors.name && (
            <p className='mt-1 text-xs text-red-500'>{errors.name.message}</p>
          )}
        </div>

        <div>
          <label className='block text-sm font-medium text-zinc-700 mb-1'>
            Email
          </label>
          <input
            {...register('email')}
            type='email'
            autoComplete='email'
            className='w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-500'
          />
          {errors.email && (
            <p className='mt-1 text-xs text-red-500'>{errors.email.message}</p>
          )}
        </div>

        <div>
          <label className='block text-sm font-medium text-zinc-700 mb-1'>
            Password
          </label>
          <input
            {...register('password')}
            type='password'
            autoComplete='new-password'
            className='w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-500'
          />
          {errors.password && (
            <p className='mt-1 text-xs text-red-500'>
              {errors.password.message}
            </p>
          )}
        </div>

        <div>
          <label className='block text-sm font-medium text-zinc-700 mb-1'>
            Confirm password
          </label>
          <input
            {...register('password_confirmation')}
            type='password'
            autoComplete='new-password'
            className='w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-500'
          />
          {errors.password_confirmation && (
            <p className='mt-1 text-xs text-red-500'>
              {errors.password_confirmation.message}
            </p>
          )}
        </div>

        {serverError && (
          <p className='text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2'>
            {serverError}
          </p>
        )}

        <button
          type='submit'
          disabled={isSubmitting}
          className='w-full rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 disabled:opacity-50'
        >
          {isSubmitting ? 'Creating account…' : 'Create account'}
        </button>
      </form>

      <p className='mt-6 text-center text-sm text-zinc-500'>
        Already have an account?{' '}
        <Link
          href='/login'
          className='font-medium text-zinc-900 hover:underline'
        >
          Sign in
        </Link>
      </p>
    </>
  );
}
