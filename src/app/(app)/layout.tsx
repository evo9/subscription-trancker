'use client';

import { useEffect, useSyncExternalStore } from 'react';
import { useRouter } from 'next/navigation';

const subscribe = () => () => {};

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
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
  return <>{children}</>;
}
