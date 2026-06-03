@once
@push('styles')
<style>
    .admin-page { max-width: 1200px; margin: 0 auto; }

    .admin-hero {
        background: linear-gradient(135deg, #1a252f 0%, #2c3e50 45%, #34495e 100%);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        color: #fff;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 24px rgba(44, 62, 80, 0.25);
    }
    .admin-hero h1, .admin-hero h4 { color: #fff; font-weight: 700; font-size: 1.25rem; margin: 0; }
    .admin-hero p { color: rgba(255,255,255,0.75); font-size: 0.85rem; margin: 0.35rem 0 0; }

    .admin-stat {
        background: #fff;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        height: 100%;
    }
    .admin-stat .num { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
    .admin-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; }
    .admin-stat.stat-ok .num { color: #27ae60; }
    .admin-stat.stat-warn .num { color: #f39c12; }
    .admin-stat.stat-danger .num { color: #e74c3c; }

    .admin-filters {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .admin-filters-toggle {
        width: 100%;
        border: none;
        background: #f8f9fa;
        padding: 0.9rem 1rem;
        font-weight: 600;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #2c3e50;
    }
    .admin-filters-body { padding: 1rem; }
    @media (min-width: 992px) {
        .admin-filters-toggle { display: none; }
        .admin-filters-body { display: block !important; padding: 1.25rem; }
    }

    .admin-table-wrap {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .admin-table { margin: 0; font-size: 0.875rem; }
    .admin-table thead th {
        background: #2c3e50;
        color: #fff;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: none;
        padding: 0.75rem 0.65rem;
        white-space: nowrap;
    }
    .admin-table tbody td { padding: 0.75rem 0.65rem; vertical-align: middle; }
    .admin-table tbody tr:hover { background: #f8f9fc; }
    .admin-table tr.row-falso { background: rgba(231, 76, 60, 0.06); }

    .admin-inc-card {
        background: #fff;
        border-radius: 16px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border-left: 4px solid #3498db;
    }
    .admin-inc-card.is-falso { border-left-color: #e74c3c; background: #fffafa; }
    .admin-inc-card.is-validado { border-left-color: #27ae60; }
    .admin-inc-card.is-pendiente { border-left-color: #f39c12; }

    .admin-badge-estado {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25em 0.6em;
        border-radius: 20px;
    }

    .admin-actions .btn {
        min-width: 2.5rem;
        min-height: 2.5rem;
        padding: 0.35rem 0.5rem;
        border-radius: 10px;
    }

    .admin-form-section {
        background: #fff;
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }
    .admin-form-section h6 {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #ecf0f1;
    }
    .admin-form-section h6 i { color: #3498db; margin-right: 0.35rem; }

    .admin-check-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.85rem;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
        min-height: 44px;
    }
    .admin-check-pill:has(input:checked) {
        border-color: #3498db;
        background: #e8f4fc;
    }
    .admin-check-pill input { width: 1.1rem; height: 1.1rem; margin: 0; }

    .admin-sticky-actions {
        position: sticky;
        bottom: 72px;
        z-index: 100;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(8px);
        padding: 0.75rem;
        margin: 0 -0.5rem;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    @media (min-width: 768px) {
        .admin-sticky-actions {
            position: static;
            background: transparent;
            box-shadow: none;
            padding: 0;
            margin: 1.5rem 0 0;
        }
    }

    .admin-back {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    .admin-back:hover { color: #2c3e50; }

    .admin-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }
    .admin-empty i { font-size: 3rem; opacity: 0.35; }

    .pagination { flex-wrap: wrap; justify-content: center; gap: 0.25rem; }
    .page-link { border-radius: 8px !important; margin: 0 2px; }
</style>
@endpush
@endonce
