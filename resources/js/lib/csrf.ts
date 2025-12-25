// CSRF helper functions
export function getCsrfToken(): string | null {
  const token = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
  return token ? token.content : null;
}

export function getCSRFHeaders(): Record<string, string> {
  const token = getCsrfToken();
  return token ? {
    'X-CSRF-TOKEN': token,
    'X-Requested-With': 'XMLHttpRequest'
  } : {
    'X-Requested-With': 'XMLHttpRequest'
  };
}

// Safe fetch with automatic CSRF token
export async function safeFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const headers = {
    'Content-Type': 'application/json',
    ...getCSRFHeaders(),
    ...options.headers,
  };

  return fetch(url, {
    ...options,
    headers,
  });
}