import { useQuery } from '@tanstack/react-query';
import api from './api';
import type { User } from '@/types/api';

export const queryKeys = {
  currentUser: ['currentUser'] as const,
};

export function useCurrentUser() {
  return useQuery<User>({
    queryKey: queryKeys.currentUser,
    queryFn: async () => {
      const { data } = await api.get<User>('/api/user');
      return data;
    },
    enabled: typeof window !== 'undefined' && !!localStorage.getItem('token'),
    retry: false,
  });
}
