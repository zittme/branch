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
</div>
