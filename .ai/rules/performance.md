---
paths:
  - app/**
---

# Performance

- **No N+1 queries.** Eager-load relations (`with`, `load`) — ideally inside the repository so callers
  cannot regress. Watch loops that access relations.
- **Paginate lists.** Never render or serialize an unbounded collection; use `paginate()`/`cursorPaginate()`
  for index screens and large API reads. Select only the columns you need.
- **Index what you filter/join/sort on.** Add the migration index alongside the query that needs it.
- **Defer heavy work.** Move email, notifications, exports, and external calls to queued jobs; keep the
  request fast.
- **Cache deterministic reads** with a clear key and TTL; invalidate on write. Do not cache per-request
  authorization decisions.
- Prefer database aggregation (`count`, `sum`, `exists`) over loading rows to count them in PHP.
- Measure before optimizing — use Telescope (local) to find the slow query or duplicated call rather
  than guessing.
