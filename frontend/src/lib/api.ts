const API_BASE_URL =
  import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000';

export interface StreamOptions {
  prompt: string;
  signal?: AbortSignal;
}

export async function streamAiResponse({
  prompt,
  signal,
}: StreamOptions): Promise<ReadableStream<Uint8Array>> {
  const response = await fetch(`${API_BASE_URL}/api/ai/stream`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'text/event-stream',
    },
    body: JSON.stringify({ prompt }),
    signal,
  });

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    throw new Error(
      body?.error ?? `Request failed with status ${response.status}`
    );
  }

  if (!response.body) {
    throw new Error('Response body is not readable');
  }

  return response.body;
}
