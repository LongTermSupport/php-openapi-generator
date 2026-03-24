# Hooks Daemon - Active Configuration

> Generated on 2026-03-24 (v2.27.0) by `generate-docs`. Regenerate: `$PYTHON -m claude_code_hooks_daemon.daemon.cli generate-docs`

## Plan Mode

> Write plans DIRECTLY to project version control.

**Plan location**: `CLAUDE/Plan/{number}-{name}/PLAN.md`
**Next number**: Scan `CLAUDE/Plan/` (including `Completed/`) for highest number, increment.

The redirect handler intercepts `~/.claude/plans/` writes as a safety net only.

## Active Handlers

### PreToolUse (18 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_pre_tool_use | NON-TERMINAL | Simple test handler that confirms PreToolUse hook is working |
| 10 | destructive_git | BLOCKING | Block destructive git commands that permanently destroy data |
| 11 | daemon_location_guard | BLOCKING | Prevent agents from cd-ing into .claude/hooks-daemon and running commands |
| 11 | sed_blocker | BLOCKING | Block sed used for file modification - Claude gets sed wrong and causes file destruction |
| 12 | absolute_path | BLOCKING | Require absolute paths for Read/Write/Edit tool file_path parameters |
| 13 | error_hiding_blocker | BLOCKING | Block error-hiding patterns in code written via Write or Edit tools |
| 14 | curl_pipe_shell | TERMINAL | Block curl/wget piped to shell commands |
| 15 | pipe_blocker | BLOCKING | Block expensive commands piped to tail/head to prevent information loss |
| 15 | security_antipattern | BLOCKING | Block Write/Edit of files containing security antipatterns |
| 16 | worktree_file_copy | BLOCKING | Prevent copying files between worktrees and main repo |
| 17 | git_stash | BLOCKING | Block or warn about git stash based on mode configuration |
| 18 | dangerous_permissions | TERMINAL | Block chmod 777 and dangerous permission commands |
| 19 | lock_file_edit_blocker | TERMINAL | Block direct editing of package manager lock files |
| 20 | pip_break_system | TERMINAL | Block pip install --break-system-packages commands |
| 21 | sudo_pip | TERMINAL | Block sudo pip install commands |
| 40 | gh_issue_comments | BLOCKING | Ensure gh issue view commands always include --comments flag |
| 42 | global_npm_advisor | NON-TERMINAL | Advise on global npm/yarn package installations |
| 55 | web_search_year | ADVISORY | Validate WebSearch queries don't use outdated years |

### PostToolUse (2 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_post_tool_use | NON-TERMINAL | Simple test handler that confirms PostToolUse hook is working |
| 10 | bash_error_detector | ADVISORY | Detect errors and warnings in Bash command output |

### SessionStart (6 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_session_start | NON-TERMINAL | Simple test handler that confirms SessionStart hook is working |
| 10 | yolo_container_detection | ADVISORY | Detects YOLO container environments using multi-tier confidence scoring |
| 52 | optimal_config_checker | ADVISORY | Check Claude Code environment for optimal configuration on session start |
| 53 | git_filemode_checker | ADVISORY | Warn when git core.fileMode=false is detected |
| 55 | suggest_status_line | ADVISORY | Suggest setting up daemon-based statusline on session start |
| 56 | version_check | ADVISORY | Check daemon version against latest GitHub release on new sessions |

### SessionEnd (2 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_session_end | NON-TERMINAL | Simple test handler that confirms SessionEnd hook is working |
| 10 | cleanup | NON-TERMINAL | Clean up temporary files when session ends |

### PreCompact (1 handler)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_pre_compact | NON-TERMINAL | Simple test handler that confirms PreCompact hook is working |

### UserPromptSubmit (3 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_user_prompt_submit | NON-TERMINAL | Simple test handler that confirms UserPromptSubmit hook is working |
| 10 | git_context_injector | CONTEXT | Inject current git status as context when user submits a prompt |
| 54 | post_clear_auto_execute | ADVISORY | Inject execution guidance on the first prompt of a new session |

### PermissionRequest (2 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_permission_request | NON-TERMINAL | Simple test handler that confirms PermissionRequest hook is working |
| 10 | auto_approve_reads | TERMINAL | Auto-approve read-only tool permission requests |

### Notification (1 handler)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_notification | NON-TERMINAL | Simple test handler that confirms Notification hook is working |

### Stop (7 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_stop | NON-TERMINAL | Simple test handler that confirms Stop hook is working |
| 5 | hello_world_subagent_stop | NON-TERMINAL | Simple test handler that confirms SubagentStop hook is working |
| 10 | auto_continue_stop | TERMINAL | Auto-continue when Claude asks confirmation questions |
| 30 | hedging_language_detector | ADVISORY | Detect hedging language that signals guessing instead of researching |
| 58 | dismissive_language_detector | ADVISORY | Detect dismissive language that signals avoiding work |
| 100 | remind_prompt_library | ADVISORY | Remind to capture successful prompts to the library |
| 100 | subagent_completion_logger | NON-TERMINAL | Log subagent completion events to a JSONL file |

### SubagentStop (1 handler)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 5 | hello_world_subagent_stop | NON-TERMINAL | Simple test handler that confirms SubagentStop hook is working |

### Status (9 handlers)

| Priority | Handler | Behavior | Description |
|----------|---------|----------|-------------|
| 10 | model_context | NON-TERMINAL | Format model name with effort level and color-coded context percentage |
| 12 | thinking_mode | NON-TERMINAL | Display thinking mode and effort level in status line |
| 14 | current_time | NON-TERMINAL | Display current local time in status line (24-hour format, no seconds) |
| 20 | git_branch | NON-TERMINAL | Show current git branch if in a git repo |
| 25 | git_repo_name | NON-TERMINAL | Show git repository name at start of status line |
| 25 | working_directory | NON-TERMINAL | Display working directory when it differs from project root |
| 30 | daemon_stats | NON-TERMINAL | Show daemon health: uptime, memory, last error, log level |
| 40 | account_display | NON-TERMINAL | Display Claude account username in status line |
| 60 | usage_tracking | NON-TERMINAL | Display daily and weekly token usage percentages |

## Quick Config Reference

**Config file**: `.claude/hooks-daemon.yaml`
**Enable/disable**: Set `enabled: true/false` under handler name
**Handler options**: Set under `options:` key per handler
