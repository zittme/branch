@include('_shell')
@include('_langfield_assets')

@php
$br_srl = (int)$form_branch_srl;
$br_days = explode(',', (string)$lang->branch_weekday);
@endphp

<div class="bra">
	<form action="{{ getUrl('') }}" method="post" id="braForm">
		<input type="hidden" name="module" value="admin" />
		<input type="hidden" name="act" value="procBranchAdminInsert" />
		<input type="hidden" name="branch_srl" value="{{ $br_srl }}" />

		<div class="bra-panel">
			<h3>{{ $lang->branch }}</h3>
			<div class="bra-grid">
				<div>
					<label>{{ $lang->branch_name }}</label>
					<div class="zlf-row-wrap"><input type="text" name="name" value="{{ $branch->name ?? '' }}" required />@include('_langfield', ['lf_name' => 'name', 'lf_value' => $branch->name_raw ?? ($branch->name ?? '')])</div>
				</div>
				<div>
					<label>{{ $lang->branch_code }}</label>
					<input type="text" name="code" value="{{ $branch->code ?? '' }}" />
					<small>{{ $lang->about_branch_code }}</small>
				</div>
				<div>
					<label>{{ $lang->branch_region }}</label>
					<div class="zlf-row-wrap"><input type="text" name="region" value="{{ $branch->region ?? '' }}" />@include('_langfield', ['lf_name' => 'region', 'lf_value' => $branch->region_raw ?? ($branch->region ?? '')])</div>
					<small>{{ $lang->about_branch_region }}</small>
				</div>
				<div>
					<label>{{ $lang->branch_tel }}</label>
					<input type="text" name="tel" value="{{ $branch->tel ?? '' }}" />
				</div>
				<div>
					<label>{{ $lang->branch_status }}</label>
					<select name="status">
						<option value="open" @if(($branch->status ?? 'open') === 'open') selected="selected" @endif>{{ $lang->branch_status_open }}</option>
						<option value="ready" @if(($branch->status ?? '') === 'ready') selected="selected" @endif>{{ $lang->branch_status_ready }}</option>
						<option value="closed" @if(($branch->status ?? '') === 'closed') selected="selected" @endif>{{ $lang->branch_status_closed }}</option>
					</select>
				</div>
				<div>
					<label>{{ $lang->branch_list_order }}</label>
					<input type="number" name="list_order" value="{{ (int)($branch->list_order ?? 0) }}" />
				</div>
			</div>

			<div class="bra-field" style="margin-top:14px">
				<label>{{ $lang->branch_summary }}</label>
				<div class="zlf-row-wrap"><input type="text" name="summary" value="{{ $branch->summary ?? '' }}" />@include('_langfield', ['lf_name' => 'summary', 'lf_value' => $branch->summary_raw ?? ($branch->summary ?? '')])</div>
			</div>
			<div class="bra-field bra-editor">
				<label>{{ $lang->branch_content }}</label>
				{{-- 에디터는 같은 이름의 필드에서 기존 내용을 읽는다. 이 칸이 없으면 늘 빈 채로 열린다 --}}
				<textarea name="content" id="braContent" style="display:none">{{ $branch->content ?? '' }}</textarea>
				{!! $editor !!}
			</div>
		</div>

		<div class="bra-panel">
			<h3>{{ $lang->branch_address }}</h3>
			<div class="bra-grid">
				<div>
					<label>{{ $lang->branch_postcode }}</label>
					<input type="text" name="postcode" value="{{ $branch->postcode ?? '' }}" />
				</div>
				<div style="grid-column:span 2">
					<label>{{ $lang->branch_address }}</label>
					<input type="text" name="address" value="{{ $branch->address ?? '' }}" />
				</div>
				<div>
					<label>{{ $lang->branch_address_detail }}</label>
					<div class="zlf-row-wrap"><input type="text" name="address_detail" value="{{ $branch->address_detail ?? '' }}" />@include('_langfield', ['lf_name' => 'address_detail', 'lf_value' => $branch->address_detail_raw ?? ($branch->address_detail ?? '')])</div>
				</div>
				<div>
					<label>{{ $lang->branch_lat }}</label>
					<input type="text" name="lat" value="{{ $branch->lat ?? '' }}" />
				</div>
				<div>
					<label>{{ $lang->branch_lng }}</label>
					<input type="text" name="lng" value="{{ $branch->lng ?? '' }}" />
					<small>{{ $lang->about_branch_coords }}</small>
				</div>
			</div>
		</div>

		<div class="bra-panel">
			<h3>{{ $lang->branch_hours }}</h3>
			<p style="margin:-10px 0 14px;font-size:13px;color:#6b7684">{{ $lang->about_branch_hours }}</p>

			<table class="bra-table bra-hours">
				<thead>
					<tr>
						<th style="width:90px"></th>
						<th>{{ $lang->branch_open_time }}</th>
						<th>{{ $lang->branch_close_time }}</th>
						<th>{{ $lang->branch_break }}</th>
						<th style="width:80px">{{ $lang->branch_day_closed }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($br_days as $wd => $wd_label)
					@php
					$h = $hours[$wd] ?? null;
					@endphp
					<tr>
						<td><strong>{{ $wd_label }}</strong></td>
						<td><input type="time" name="open_time[{{ $wd }}]" value="{{ $h->open_time ?? '' }}" /></td>
						<td><input type="time" name="close_time[{{ $wd }}]" value="{{ $h->close_time ?? '' }}" /></td>
						<td style="white-space:nowrap">
							<input type="time" name="break_start[{{ $wd }}]" value="{{ $h->break_start ?? '' }}" style="display:inline-block;width:auto" />
							<input type="time" name="break_end[{{ $wd }}]" value="{{ $h->break_end ?? '' }}" style="display:inline-block;width:auto" />
						</td>
						<td>
							<input type="checkbox" name="day_closed[]" value="{{ $wd }}" @if($h && (string)$h->is_closed === 'Y') checked="checked" @endif />
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="bra-panel">
			<h3>{{ $lang->branch_images }}</h3>
			<div class="bra-field">
				<label>{{ $lang->branch_thumb }}</label>
				<input type="text" name="thumb" value="{{ $branch->thumb ?? '' }}" />
			</div>
			<div class="bra-field">
				<label>{{ $lang->branch_images }}</label>
				<textarea name="images_text" rows="4">{{ $images_text }}</textarea>
				<small>{{ $lang->about_branch_images }}</small>
			</div>
			<div class="bra-field">
				<label>{{ $lang->branch_amenities }}</label>
				<div class="zlf-row-wrap"><input type="text" name="amenities" value="{{ $branch->amenities ?? '' }}" />@include('_langfield', ['lf_name' => 'amenities', 'lf_value' => $branch->amenities_raw ?? ($branch->amenities ?? '')])</div>
				<small>{{ $lang->about_branch_amenities }}</small>
			</div>
		</div>

		<div style="text-align:right;margin-bottom:20px">
			<a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBranchAdminList') }}" class="bra-btn">{{ $lang->cmd_list }}</a>
			<button type="submit" class="bra-btn bra-btn-primary">{{ $lang->cmd_save }}</button>
		</div>
	</form>

	@if ($br_srl > 0)
	<div class="bra-panel">
		<h3>{{ $lang->branch_holidays }}</h3>

		<form action="{{ getUrl('') }}" method="post" class="bra-inline" style="margin-bottom:16px">
			<input type="hidden" name="module" value="admin" />
			<input type="hidden" name="act" value="procBranchAdminInsertHoliday" />
			<input type="hidden" name="branch_srl" value="{{ $br_srl }}" />
			<div>
				<label>{{ $lang->branch_holiday_type }}</label>
				<select name="repeat_type" id="braRepeat">
					<option value="once">{{ $lang->branch_holiday_once }}</option>
					<option value="monthly_nth">{{ $lang->branch_holiday_monthly }}</option>
				</select>
			</div>
			<div data-when="once">
				<label>{{ $lang->branch_holiday_date }}</label>
				<input type="text" name="holiday_date" placeholder="YYYYMMDD" />
			</div>
			<div data-when="monthly_nth" style="display:none">
				<label>{{ $lang->branch_holiday_nth }}</label>
				<select name="nth">
					@for ($i = 1; $i <= 5; $i++)
					<option value="{{ $i }}">{{ $i }}</option>
					@endfor
				</select>
			</div>
			<div data-when="monthly_nth" style="display:none">
				<label>{{ $lang->branch_holiday_weekday }}</label>
				<select name="weekday">
					@foreach ($br_days as $wd => $wd_label)
					<option value="{{ $wd }}">{{ $wd_label }}</option>
					@endforeach
				</select>
			</div>
			<div style="flex:1;min-width:160px">
				<label>{{ $lang->branch_holiday_reason }}</label>
				<input type="text" name="reason" />
			</div>
			<div>
				<button type="submit" class="bra-btn bra-btn-primary">{{ $lang->cmd_add }}</button>
			</div>
		</form>

		@if (empty($holidays))
		<p class="bra-empty">{{ $lang->branch_no_holiday }}</p>
		@else
		<table class="bra-table">
			<thead><tr><th>{{ $lang->branch_holiday_type }}</th><th>{{ $lang->branch_holiday_date }}</th><th>{{ $lang->branch_holiday_reason }}</th><th></th></tr></thead>
			<tbody>
				@foreach ($holidays as $hd)
				@php
				$hd_once = (string)$hd->repeat_type === 'once';
				$hd_when = $hd_once ? (string)$hd->holiday_date : ((int)$hd->nth . ' / ' . ($br_days[(int)$hd->weekday] ?? ''));
				@endphp
				<tr>
					<td>{{ $hd_once ? $lang->branch_holiday_once : $lang->branch_holiday_monthly }}</td>
					<td>{{ $hd_when }}</td>
					<td>{{ $hd->reason }}</td>
					<td style="text-align:right">
						<form action="{{ getUrl('') }}" method="post" style="display:inline">
							<input type="hidden" name="module" value="admin" />
							<input type="hidden" name="act" value="procBranchAdminDeleteHoliday" />
							<input type="hidden" name="branch_srl" value="{{ $br_srl }}" />
							<input type="hidden" name="holiday_srl" value="{{ (int)$hd->holiday_srl }}" />
							<button type="submit" class="bra-btn bra-btn-sm bra-btn-danger">{{ $lang->cmd_delete }}</button>
						</form>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
	@endif
</div>

<script>
(function () {
	/*
	  코어 에디터는 제출할 때 내용을 스스로 옮기지 않는다.
	  옮기지 않으면 빈 값이 저장되어 기존 소개가 통째로 지워진다.
	*/
	var form = document.getElementById('braForm');
	if (!form) { return; }

	/* 에디터 스킨마다 시퀀스를 두는 자리가 다르다. 둘 다 본다 */
	function editorSequence() {
		var input = form.querySelector('input[name="editor_sequence"]');
		if (input && input.value) { return input.value; }

		var box = form.querySelector('[data-editor-sequence]');
		return box ? box.getAttribute('data-editor-sequence') : null;
	}

	form.addEventListener('submit', function () {
		var seq = editorSequence();
		var box = document.getElementById('braContent');
		if (seq && box && typeof editorGetContent === 'function') {
			box.value = editorGetContent(seq);
		}
	});
})();

(function () {
	var sel = document.getElementById('braRepeat');
	if (!sel) { return; }
	function sync() {
		document.querySelectorAll('[data-when]').forEach(function (el) {
			el.style.display = el.getAttribute('data-when') === sel.value ? '' : 'none';
		});
	}
	sel.addEventListener('change', sync);
	sync();
})();
</script>
