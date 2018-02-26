# concrete5 Add-on: Batch User Update

Update all user attributes from command line.

## Installation

```bash
$ cd YOUR_PROJECT_ROOT
$ git clone git@github.com:hissy/addon_batch_user_update.git packages/batch_user_update
$ ./concrete/bin/concrete5 c5:package-install simple_database_export
```

# How to Use

- Simply run `./concrete/bin/concrete5 c5:update-user` from your project root directory.
- Make sure to change `updateUser()` method as your requirements on file `packages/batch_user_update/src/Concrete/Console/Command/UpdateUserCommand.php#126`.