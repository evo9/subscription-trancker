export interface User {
  id: number;
  name: string;
  email: string;
}

export interface AuthResponse {
  token: string;
  user?: User;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export type BillingCycle = 'weekly' | 'monthly' | 'quarterly' | 'yearly';
export type SubscriptionStatus = 'active' | 'paused' | 'cancelled';

export interface Category {
  id: number;
  name: string;
  color: string;
  created_at: string;
  updated_at: string;
}

export interface Payment {
  id: number;
  subscription_id: number;
  amount: string;
  currency: string;
  paid_at: string;
  created_at: string;
}

export interface Subscription {
  id: number;
  name: string;
  description: string | null;
  price: string;
  currency: string;
  billing_cycle: BillingCycle;
  status: SubscriptionStatus;
  started_at: string;
  next_billing_date: string;
  cancelled_at: string | null;
  notify_days_before: number;
  monthly_cost: number;
  yearly_cost: number;
  category: { id: number; name: string; color: string } | null;
  payments?: Payment[];
  created_at: string;
  updated_at: string;
}

export interface CategoryBreakdown {
  category_id: number | null;
  name: string | null;
  color: string | null;
  monthly_total: number;
  yearly_total: number;
}

export interface StatsSummary {
  monthly_total: number;
  yearly_total: number;
  by_category: CategoryBreakdown[];
}

export interface NotificationData {
  subscription_id: number;
  subscription_name: string;
  amount: string;
  currency: string;
  next_billing_date: string;
}

export interface AppNotification {
  id: string;
  data: NotificationData;
  read_at: string | null;
  created_at: string;
}

export interface SubscriptionFilters {
  status?: SubscriptionStatus;
  category_id?: number;
  due_within?: number;
}

export interface SubscriptionFormValues {
  name: string;
  description?: string | null;
  category_id?: number | null;
  price: string;
  currency: string;
  billing_cycle: BillingCycle;
  started_at: string;
  next_billing_date: string;
  notify_days_before: number;
}
