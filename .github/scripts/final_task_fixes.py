from pathlib import Path

path = Path('app/Http/Controllers/TaskController.php')
text = path.read_text()

def replace_once(old: str, new: str) -> None:
    global text
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'expected one match, found {count}: {old[:120]!r}')
    text = text.replace(old, new, 1)

replace_once(
    """        DB::beginTransaction();
        $ganttTaskArray = [];
        $gantTaskLinkArray = [];

        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();
        $task = new Task();""",
    """        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();

        if (!$taskBoardColumn) {
            return Reply::error(__('messages.selectStatus'));
        }

        DB::beginTransaction();
        $ganttTaskArray = [];
        $gantTaskLinkArray = [];

        $task = new Task();"""
)

replace_once(
    """            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                /* @phpstan-ignore-line */
                return Reply::error(__('messages.taskDependentDate'));
            }""",
    """            if (!is_null($dependentTask->due_date) && !is_null($dueDate) && $dependentTask->due_date->greaterThan($dueDate)) {
                DB::rollBack();
                /* @phpstan-ignore-line */
                return Reply::error(__('messages.taskDependentDate'));
            }"""
)

replace_once(
    """        if($request->select_value == 'Waiting Approval'){

            $taskBoardColumn = TaskboardColumn::where('column_name', $request->select_value)->where('company_id', company()->id)->first();
            $task->board_column_id = $taskBoardColumn->id;
            $task->approval_send = 1;
        }""",
    """        if ($request->select_value == 'Waiting Approval') {
            $taskBoardColumn = TaskboardColumn::where('column_name', $request->select_value)
                ->where('company_id', company()->id)
                ->first();

            if (!$taskBoardColumn) {
                return Reply::error(__('messages.selectStatus'));
            }

            $task->board_column_id = $taskBoardColumn->id;
            $task->approval_send = 1;
        }"""
)

replace_once(
    "$task->approval_send = $request->isApproval ?? 0;",
    "$task->approval_send = $request->boolean('isApproval') ? 1 : 0;"
)

path.write_text(text)
