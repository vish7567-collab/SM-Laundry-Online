export function makeEvent(request) {
  const url = new URL(request.url);
  return request.text().then(body => ({
    httpMethod: request.method,
    headers: Object.fromEntries(request.headers.entries()),
    queryStringParameters: Object.fromEntries(url.searchParams.entries()),
    body
  }));
}
export function toResponse(result) {
  const headers = new Headers(result?.headers || {});
  return new Response(result?.body ?? '', { status: result?.statusCode || 500, headers });
}
