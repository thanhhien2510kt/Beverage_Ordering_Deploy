---
description: Sync code with remote Git repository (Push/Pull)
---

This workflow helps in pushing all local changes to a remote repository or pulling the latest changes from it.

### // turbo-all
### 1. Push all changes to Git
1. Check current status: `git status`
2. Add all changes: `git add .`
3. Commit changes: `git commit -m "Update from AI Assistant"`
4. Identify branches: `git branch`
5. Push to remote (e.g., meowtea): `git push [remote] [local-branch]:[remote-branch]`
   - Note: If pushing to a new repository, ensure remotes are set up first: `git remote -v`

### 2. Pull changes from Git
1. Fetch from remote: `git fetch [remote]`
2. Pull and merge: `git pull [remote] [branch]`
3. Resolve any conflicts if necessary.

### 3. Handle submodules (Special Case)
If a directory is not being tracked because of nested `.git` folders:
1. Check tracking status: `git ls-files --stage [path]`
2. Remove cached submodule link if exists: `git rm --cached [path]`
3. Ensure no `.git` folder exists inside the subdirectory: `rm -rf [path]/.git`
4. Add the files as regular content: `git add [path]`
