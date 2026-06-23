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
