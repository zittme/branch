<style>
/* 지점 관리 화면 - Pretendard / #2677e3, 관리자 리디자인 톤 */
.bra { font-family: 'Pretendard Variable', Pretendard, -apple-system, BlinkMacSystemFont, system-ui, sans-serif; word-break: keep-all; color: #1c2330; }
.bra-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e8ee; border-radius: 12px; overflow: hidden; }
.bra-table th { padding: 11px 13px; background: #f7f8fa; font-size: 13px; font-weight: 600; color: #6b7684; text-align: left; border-bottom: 1px solid #e5e8ee; }
.bra-table td { padding: 12px 13px; font-size: 13px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; color: #1c2330; }
.bra-table tr:last-child td { border-bottom: 0; }
.bra-panel { padding: 20px 22px; border: 1px solid #e5e8ee; border-radius: 14px; background: #fff; margin-bottom: 16px; }
.bra-panel h3 { margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #f0f2f5; font-size: 15px; font-weight: 700; }
.bra-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
.bra-field { margin-bottom: 14px; }
.bra label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #333d4b; }
.bra input[type="text"], .bra input[type="number"], .bra input[type="time"], .bra select, .bra textarea { width: 100%; box-sizing: border-box; padding: 9px 11px; border: 1px solid #dde3ec; border-radius: 9px; font-size: 13.5px; font-family: inherit; background: #fff; color: #1c2330; }
.bra input:focus, .bra select:focus, .bra textarea:focus { outline: none; border-color: #2677e3; box-shadow: 0 0 0 3px rgba(38,119,227,.12); }
.bra small { display: block; margin-top: 5px; font-size: 12px; color: #8b95a1; font-weight: 400; }
/* 관리자 전역 a/버튼 색 규칙이 특이도로 덮으므로 색을 고정한다 */
.bra-btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border: 1px solid #dde3ec; border-radius: 9px; background: #fff !important; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; color: #1c2330 !important; text-decoration: none !important; }
.bra-btn:hover { border-color: #2677e3; color: #2677e3 !important; }
.bra-btn-primary { background: #2677e3 !important; border-color: #2677e3; color: #fff !important; }
.bra-btn-primary:hover { filter: brightness(1.06); color: #fff !important; }
.bra-btn-sm { padding: 5px 10px; font-size: 12.5px; border-radius: 7px; }
.bra-btn-danger:hover { border-color: #e5484d; color: #e5484d !important; }
.bra-st { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f2f3f5; color: #6b7684; }
.bra-st-open { background: rgba(38,119,227,.1); color: #2677e3; }
.bra-st-ready { background: #fdf3e2; color: #b97a17; }
.bra-empty { padding: 34px 0; text-align: center; color: #6b7684; font-size: 13px; }
.bra-hours td { padding: 8px 10px; }
.bra-hours input[type="time"] { max-width: 130px; }
.bra-inline { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.bra-inline > div { min-width: 110px; }
@media (max-width: 768px) { .bra-table { display: block; overflow-x: auto; } }
</style>

<div class="x_page-header bra">
	<h1>{{ $lang->branch_list }}</h1>
</div>
