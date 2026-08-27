from pathlib import Path

path = Path('app/Http/Controllers/HomeController.php')
text = path.read_text()

def replace_once(old: str, new: str) -> None:
    global text
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'expected one match, found {count}: {old[:140]!r}')
    text = text.replace(old, new, 1)

replace_once(
    """        $leadPipeline = LeadPipeline::where('default', '1')->where('company_id', $company->id)->first();
        $leadStage = PipelineStage::where('default', '1')->where('lead_pipeline_id', $leadPipeline->id)->where('company_id', $company->id)->first();""",
    """        $leadPipeline = LeadPipeline::where('default', '1')->where('company_id', $company->id)->first()
            ?: LeadPipeline::where('company_id', $company->id)->orderBy('id')->first();

        if (!$leadPipeline) {
            return Reply::error('Lead pipeline is not configured for this company.');
        }

        $leadStage = PipelineStage::where('default', '1')
            ->where('lead_pipeline_id', $leadPipeline->id)
            ->where('company_id', $company->id)
            ->first()
            ?: PipelineStage::where('lead_pipeline_id', $leadPipeline->id)
                ->where('company_id', $company->id)
                ->orderBy('id')
                ->first();

        if (!$leadStage) {
            return Reply::error('Lead pipeline stage is not configured for this company.');
        }"""
)

replace_once(
    "$leadContact = Lead::where('client_email', $request->email)->first();",
    "$leadContact = Lead::where('client_email', $request->email)->where('company_id', $company->id)->first();"
)

replace_once(
    "$existing_user = User::withoutGlobalScope(ActiveScope::class)->select('id', 'email')->where('email', $request->email)->first();",
    "$existing_user = User::withoutGlobalScope(ActiveScope::class)->select('id', 'email')->where('company_id', $company->id)->where('email', $request->email)->first();"
)

replace_once(
    """        if (!$existing_user) {
            $password = str_random(8);""",
    """        if (!$existing_user) {
            $role = Role::withoutGlobalScope(CompanyScope::class)
                ->where('name', 'client')
                ->where('company_id', $company->id)
                ->select('id')
                ->first();

            if (!$role) {
                return Reply::error('Client role is not configured for this company.');
            }

            $password = str_random(8);"""
)

replace_once(
    """            // attach role
            $role = Role::withoutGlobalScope(CompanyScope::class)
                ->where('name', 'client')
                ->where('company_id', $company->id)
                ->select('id')
                ->first();

            $role ? $client->attachRole($role->id) : null;""",
    """            // attach role (validated before creating the user)
            $client->attachRole($role->id);"""
)

replace_once(
    """        $applicationVersion = trim(
            preg_replace(
                '/\\s\\s+/',
                ' ',
                !file_exists(File::get(public_path() . '/version.txt')) ? File::get(public_path() . '/version.txt') : '0'
            )
        );""",
    """        $applicationVersionFile = public_path('version.txt');
        $applicationVersion = File::exists($applicationVersionFile)
            ? trim(preg_replace('/\\s\\s+/', ' ', File::get($applicationVersionFile)))
            : '0';"""
)

replace_once(
    """        foreach ($plugins as $plugin) {
            $enableModules[$plugin->getName()] = trim(
                preg_replace(
                    '/\\s\\s+/',
                    ' ',
                    !file_exists(File::get($plugin->getPath() . '/version.txt')) ? File::get($plugin->getPath() . '/version.txt') : '0'
                )
            );
        }""",
    """        foreach ($plugins as $plugin) {
            $pluginVersionFile = $plugin->getPath() . '/version.txt';
            $enableModules[$plugin->getName()] = File::exists($pluginVersionFile)
                ? trim(preg_replace('/\\s\\s+/', ' ', File::get($pluginVersionFile)))
                : '0';
        }"""
)

path.write_text(text)
