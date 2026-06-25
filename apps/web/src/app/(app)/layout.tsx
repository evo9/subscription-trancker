'use client';

import { useEffect, useSyncExternalStore } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import { useQueryClient } from '@tanstack/react-query';
import Link from 'next/link';

function subscribe(callback: () => void) {
  window.addEventListener('storage', callback);
  return () => window.removeEventListener('storage', callback);
}

function NavLink({ href, label }: { href: string; label: string }) {
  const pathname = usePathname();
  const active = pathname === href || pathname.startsWith(href + '/');
  return (
    <Link
      href={href}
      className={`text-sm font-medium transition-colors ${
        active ? 'text-zinc-900' : 'text-zinc-500 hover:text-zinc-900'
      }`}
    >
      {label}
    </Link>
  );
}

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const hasToken = useSyncExternalStore(
    subscribe,
    () => !!localStorage.getItem('token'),
    () => false,
  );

  useEffect(() => {
    if (!hasToken) {
      router.replace('/login');
    }
  }, [hasToken, router]);

  if (!hasToken) return null;

  function handleLogout() {
    localStorage.removeItem('token');
    queryClient.clear();
    router.replace('/login');
  }

  return (
    <div className='min-h-screen bg-zinc-50'>
      <nav className='border-b border-zinc-200 bg-white'>
        <div className='mx-auto max-w-4xl px-4 flex items-center justify-between h-14'>
          <div className='flex items-center gap-6'>
            <span className='text-sm font-semibold text-zinc-900'>SubTracker</span>
            <NavLink href='/dashboard' label='Dashboard' />
            <NavLink href='/subscriptions' label='Subscriptions' />
            <NavLink href='/notifications' label='Notifications' />
          </div>
          <button
            onClick={handleLogout}
            className='text-sm text-zinc-500 hover:text-zinc-900 transition-colors'
          >
            Sign out
          </button>
        </div>
      </nav>
      <main className='mx-auto max-w-4xl px-4 py-8'>{children}</main>
    </div>
  );
}
