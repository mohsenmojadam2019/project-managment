<div class="row d-flex justify-content-between">
    <div>
        <div class='input-group {{ isRtl('flex-row') }}'>
            <div class="input-group-prepend" style="{{ isRtl('border-radius: 5px; margin-right: 7px;') }}">
                <button id="week-start-date" style="{{ isRtl('border-radius: 5px!important; margin-left: 7px;') }}" data-date="{{ $weekStartDate->copy()->subDay()->toDateString() }}" type="button"
                    class="btn btn-outline-secondary border-grey height-35"><i class="fa fa-chevron-right"></i>
                </button>
            </div>
            @php
                $p_weekStartDate = new \Hekmatinasser\Verta\Verta( $weekStartDate);
                $p_weekEndDate = new \Hekmatinasser\Verta\Verta($weekEndDate);
            @endphp
            @if (app()->getlocale()=='fa')
                <input type="text" disabled class="form-control height-35 f-14 bg-white text-center" value="{{ $p_weekStartDate->Format('d F') . ' - ' . $p_weekEndDate->Format('d F') }}">
            @else
                <input type="text" disabled class="form-control height-35 f-14 bg-white text-center" value="{{ $weekStartDate->translatedFormat('d M') . ' - ' . $weekEndDate->translatedFormat('d M') }}">
            @endif
           
            <div class="input-group-append">
                <button id="week-end-date" style="{{ isRtl('border-radius: 5px!important; margin-right: 7px;') }}" data-date="{{ $weekEndDate->copy()->addDay()->toDateString() }}" type="button"
                    class="btn btn-outline-secondary border-grey height-35"><i class="fa fa-chevron-left"></i>
                </button>
            </div>
        </div>

    </div>
    <div class="ml-3 align-self-center">
        @foreach ($employeeShifts as $item)
            <span class="p-1 badge badge-info f-11" style="background-color: {{ $item->color }}">
                {{ $item->shift_short_code }} : {{ $item->shift_name }}</span>
            {{ !$loop->last ? ' | ' : '' }}
        @endforeach
       | <i class="fa fa-star text-primary"></i> : @lang('app.menu.holiday')
    </div>
</div>


<div class="table-responsive">
    <x-table class="mt-3 table-bordered table-hover" headType="thead-light">
        <x-slot name="thead">
            <th class="px-2" style="vertical-align: middle;">@lang('app.employee')</th>
            @foreach ($weekPeriod->toArray() as $date)
                <th class="px-1">
                    <div class="d-flex">
                        @php
                            $verta= new \Hekmatinasser\Verta\Verta($date)
                        @endphp
                        
                        
                        @if (app()->getlocale()=='fa')
                            <div class="f-27 align-self-center mr-2">{{ $verta->Format('d') }}</div>
                            <div class="text-lightest f-11 text-uppercase">{{ $verta->Format('l') }} <br>{{ $verta->Format('F') }}</div>
                        @else
                            <div class="f-27 align-self-center mr-2">{{ $date->day }}</div>
                            <div class="text-lightest f-11 text-uppercase">{{ $date->translatedFormat('l') }} <br>{{ $date->translatedFormat('M') }}</div> 
                        @endif
                        
                    </div>
                </th>
            @endforeach
        </x-slot>

        @foreach ($employeeAttendence as $key => $attendance)
            @php
                $userId = explode('#', $key);
                $userId = $userId[0];
                $count = 1;
                $isActive = '';

                if(in_array($userId,$employeeIdsInactive)){
                    $isActive = 'no';
                }

            @endphp
            <tr>
                <td class="px-1"> {!! end($attendance) !!} </td>
                @foreach ($attendance as $key2 => $day)
                    @if ($count + 1 <= count($attendance))
                        @php
                            $attendanceDate = \Carbon\Carbon::parse($key2);
                        @endphp
                        <td class="px-1">
                            @if ($day == 'Leave')
                                @php
                                    // Retrieve the leave type for the current employee and date
                                    $currentLeaveType = isset($leaveType[$userId][$key2]) ? $leaveType[$userId][$key2] : __('modules.attendance.leave');
                                @endphp
                                <div data-toggle="tooltip" class="p-1 py-4 border badge badge-light f-10 border-danger w-100" data-original-title="@lang('modules.attendance.leave')"><i
                                        class="mr-2 fa fa-plane-departure text-red"></i>{{ $currentLeaveType }}</div>
                                @elseif ($day == 'Half Day')
                                    @if ($attendanceDate->isFuture())
                                        <div data-toggle="tooltip" class="p-1 py-4 border badge badge-warning f-10 border-danger w-100" data-original-title="@lang('modules.attendance.halfDay')"><i
                                            class="mr-2 fa fa-star-half-alt text-red"></i>@lang('modules.attendance.halfDay')</div>
                                    @else
                                        <a href="javascript:;" class="py-4 change-shift-week{{$isActive}} w-100" data-user-id="{{ $userId }}"
                                                data-attendance-date="{{ $key2 }}">
                                            <span data-toggle="tooltip" data-original-title="@lang('modules.attendance.halfDay')"><i
                                                    class="mr-2 fa fa-star-half-alt text-red"></i>@lang('modules.attendance.halfDay')</span>
                                        </a>
                                    @endif
                                @elseif ($day == 'EMPTY')
                                    <button type="button" class="p-1 py-4 border change-shift-week{{$isActive}} badge badge-light f-14 w-100"  data-user-id="{{ $userId }}"
                                        data-attendance-date="{{ $key2 }}">
                                        @if (in_array($manageEmployeeShifts, ['all']))
                                        <i class="fa fa-plus-circle text-primary"></i>
                                        @else
                                        <i class="fa fa-ban text-red"></i>
                                        @endif</button>
                                @elseif ($day == 'Holiday')
                                <div data-toggle="tooltip" class="p-1 py-4 border badge badge-light f-10 border-primary w-100 change-shift-week{{$isActive}}"
                                    data-original-title="@lang('modules.attendance.holiday')" data-user-id="{{ $userId }}" data-attendance-date="{{ $key2 }}"> <i class="fa fa-star text-primary"></i>
                                    {{ $holidayOccasions[$key2] }}</div>
                            @else
                                {!! $day !!}
                            @endif
                        </td>
                    @endif
                    @php
                        $count++;
                    @endphp
                @endforeach
            </tr>
        @endforeach
    </x-table>
</div>
