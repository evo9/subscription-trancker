import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from './api';
import type {
  AppNotification,
  Category,
  Payment,
  StatsSummary,
  Subscription,
  SubscriptionFilters,
  SubscriptionFormValues,
  User,
} from '@/types/api';

// ── Current user ──────────────────────────────────────────────────────────────

export function useCurrentUser() {
  return useQuery<User>({
    queryKey: ['currentUser'],
    queryFn: async () => {
      const { data } = await api.get<User>('/api/user');
      return data;
    },
    enabled: typeof window !== 'undefined' && !!localStorage.getItem('token'),
    retry: false,
  });
}

// ── Subscriptions ─────────────────────────────────────────────────────────────

export function useSubscriptions(filters?: SubscriptionFilters) {
  return useQuery<Subscription[]>({
    queryKey: ['subscriptions', filters ?? {}],
    queryFn: async () => {
      const { data } = await api.get<{ data: Subscription[] }>('/api/subscriptions', {
        params: filters,
      });
      return data.data;
    },
  });
}

export function useSubscription(id: number) {
  return useQuery<Subscription>({
    queryKey: ['subscription', id],
    queryFn: async () => {
      const { data } = await api.get<{ data: Subscription }>(`/api/subscriptions/${id}`);
      return data.data;
    },
    enabled: !!id,
  });
}

export function useSubscriptionPayments(id: number) {
  return useQuery<Payment[]>({
    queryKey: ['subscription', id, 'payments'],
    queryFn: async () => {
      const { data } = await api.get<{ data: Payment[] }>(
        `/api/subscriptions/${id}/payments`,
      );
      return data.data;
    },
    enabled: !!id,
  });
}

export function useCreateSubscription() {
  const qc = useQueryClient();
  return useMutation<Subscription, Error, SubscriptionFormValues>({
    mutationFn: async (values) => {
      const { data } = await api.post<{ data: Subscription }>('/api/subscriptions', values);
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['subscriptions'] });
      qc.invalidateQueries({ queryKey: ['stats'] });
    },
  });
}

export function useUpdateSubscription(id: number) {
  const qc = useQueryClient();
  return useMutation<Subscription, Error, Partial<SubscriptionFormValues>>({
    mutationFn: async (values) => {
      const { data } = await api.patch<{ data: Subscription }>(
        `/api/subscriptions/${id}`,
        values,
      );
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['subscriptions'] });
      qc.invalidateQueries({ queryKey: ['subscription', id] });
      qc.invalidateQueries({ queryKey: ['stats'] });
    },
  });
}

export function useDeleteSubscription() {
  const qc = useQueryClient();
  return useMutation<void, Error, number>({
    mutationFn: async (id) => {
      await api.delete(`/api/subscriptions/${id}`);
    },
    onSuccess: (_, id) => {
      qc.invalidateQueries({ queryKey: ['subscriptions'] });
      qc.removeQueries({ queryKey: ['subscription', id] });
      qc.invalidateQueries({ queryKey: ['stats'] });
    },
  });
}

export function usePauseSubscription() {
  const qc = useQueryClient();
  return useMutation<Subscription, Error, number>({
    mutationFn: async (id) => {
      const { data } = await api.post<{ data: Subscription }>(
        `/api/subscriptions/${id}/pause`,
      );
      return data.data;
    },
    onSuccess: (_, id) => {
      qc.invalidateQueries({ queryKey: ['subscriptions'] });
      qc.invalidateQueries({ queryKey: ['subscription', id] });
      qc.invalidateQueries({ queryKey: ['stats'] });
    },
  });
}

export function useResumeSubscription() {
  const qc = useQueryClient();
  return useMutation<Subscription, Error, number>({
    mutationFn: async (id) => {
      const { data } = await api.post<{ data: Subscription }>(
        `/api/subscriptions/${id}/resume`,
      );
      return data.data;
    },
    onSuccess: (_, id) => {
      qc.invalidateQueries({ queryKey: ['subscriptions'] });
      qc.invalidateQueries({ queryKey: ['subscription', id] });
      qc.invalidateQueries({ queryKey: ['stats'] });
    },
  });
}

// ── Categories ────────────────────────────────────────────────────────────────

export function useCategories() {
  return useQuery<Category[]>({
    queryKey: ['categories'],
    queryFn: async () => {
      const { data } = await api.get<{ data: Category[] }>('/api/categories');
      return data.data;
    },
  });
}

// ── Stats ─────────────────────────────────────────────────────────────────────

export function useStatsSummary() {
  return useQuery<StatsSummary>({
    queryKey: ['stats', 'summary'],
    queryFn: async () => {
      const { data } = await api.get<{ data: StatsSummary }>('/api/stats/summary');
      return data.data;
    },
  });
}

export function useUpcomingRenewals() {
  return useQuery<Subscription[]>({
    queryKey: ['stats', 'upcoming'],
    queryFn: async () => {
      const { data } = await api.get<{ data: Subscription[] }>('/api/stats/upcoming');
      return data.data;
    },
  });
}

// ── Notifications ─────────────────────────────────────────────────────────────

export function useNotifications() {
  return useQuery<AppNotification[]>({
    queryKey: ['notifications'],
    queryFn: async () => {
      const { data } = await api.get<{ data: AppNotification[] }>('/api/notifications');
      return data.data;
    },
  });
}

export function useMarkNotificationRead() {
  const qc = useQueryClient();
  return useMutation<void, Error, string>({
    mutationFn: async (id) => {
      await api.post(`/api/notifications/${id}/read`);
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
}
