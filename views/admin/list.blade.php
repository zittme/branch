@include('_shell')

<div class="bra">
	<div style="margin-bottom:14px;text-align:right">
		<a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBranchAdminEdit') }}" class="bra-btn bra-btn-primary">{{ $lang->branch_add }}</a>
	</div>

	@if (empty($branches))
	<p class="bra-empty">{{ $lang->branch_none }}</p>
	@else
	<table class="bra-table">
		<thead>
			<tr>
				<th>{{ $lang->branch_name }}</th>
				<th>{{ $lang->branch_region }}</th>
				<th>{{ $lang->branch_address }}</th>
				<th>{{ $lang->branch_tel }}</th>
				<th>{{ $lang->branch_hours }}</th>
				<th>{{ $lang->branch_status }}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach ($branches as $b)
			@php
			$b_srl = (int)$b->branch_srl;
			$b_status = (string)$b->status;
			$b_hours = (string)($today_hours[$b_srl] ?? '');
			$b_status_name = ['open' => $lang->branch_status_open, 'ready' => $lang->branch_status_ready, 'closed' => $lang->branch_status_closed][$b_status] ?? $b_status;
			@endphp
			<tr>
				<td>
					<strong>{{ $b->name }}</strong>
					@if ($b->code)<br /><small style="color:#9aa1ab">{{ $b->code }}</small>@endif
				</td>
				<td>{{ $b->region }}</td>
				<td>{{ trim($b->address . ' ' . $b->address_detail) }}</td>
				<td>{{ $b->tel }}</td>
				<td>{{ $b_hours ?: $lang->branch_today_closed }}</td>
				<td><span class="bra-st bra-st-{{ $b_status }}">{{ $b_status_name }}</span></td>
				<td style="text-align:right;white-space:nowrap">
					<a href="{{ getUrl('', 'module', 'admin', 'act', 'dispBranchAdminEdit', 'branch_srl', $b_srl) }}" class="bra-btn bra-btn-sm">{{ $lang->cmd_modify }}</a>
					<form action="{{ getUrl('') }}" method="post" style="display:inline" onsubmit="return confirm('{{ $lang->confirm_branch_delete }}')">
						<input type="hidden" name="module" value="admin" />
						<input type="hidden" name="act" value="procBranchAdminDelete" />
						<input type="hidden" name="branch_srl" value="{{ $b_srl }}" />
						<button type="submit" class="bra-btn bra-btn-sm bra-btn-danger">{{ $lang->cmd_delete }}</button>
					</form>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif

	<form class="bra-panel" action="./" method="post" style="margin-top:20px">
		<input type="hidden" name="module" value="branch" />
		<input type="hidden" name="act" value="procBranchAdminInsertConfig" />
		<h3>{{ $lang->branch_map_links }}</h3>
		<div class="bra-field">
			<label for="bra_map_mode">{{ $lang->branch_map_mode }}</label>
			<select id="bra_map_mode" name="map_mode" style="max-width:320px">
				@foreach (['auto', 'kakao', 'naver', 'google', 'osm', 'multi'] as $bra_mode)
				<option value="{{ $bra_mode }}" @selected($map_config->map_mode === $bra_mode)>{{ $lang->{'branch_map_' . $bra_mode} }}</option>
				@endforeach
			</select>
			<small>{{ $lang->about_branch_map_mode }}</small>
		</div>
		<div class="bra-field" id="bra_map_multi" @if ($map_config->map_mode !== 'multi') hidden @endif>
			<label>{{ $lang->branch_map_services }}</label>
			<div class="bra-inline">
				@foreach (['kakao', 'naver', 'google', 'osm'] as $bra_service)
				<span style="display:inline-flex;align-items:center;gap:6px;font-size:13.5px"><input type="checkbox" name="map_services[]" value="{{ $bra_service }}" id="bra_ms_{{ $bra_service }}" @checked(in_array($bra_service, $map_config->map_services, true)) /><label for="bra_ms_{{ $bra_service }}" style="margin:0;font-weight:500">{{ $lang->{'branch_map_' . $bra_service} }}</label></span>
				@endforeach
			</div>
		</div>
		<button type="submit" class="bra-btn bra-btn-primary">{{ $lang->cmd_save }}</button>
	</form>
	<script>
	document.getElementById('bra_map_mode').addEventListener('change', function () {
		document.getElementById('bra_map_multi').hidden = this.value !== 'multi';
	});
	</script>
</div>
