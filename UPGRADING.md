## How to merge in the latest changes from PHPTemplateProject

There is a script that will help you do this,
but to use it successfully you'd better understand how it works,
so that explanation comes first.

Example commit diagram:

```
                       YP/M
                           
             YP/RTP       ┊
                          │
       TP/M       ┊       │
                  │     ╭─●
          ┊       │ ╭───╯ │H
          │     ╭─●─╯     │
          │ ╭───╯ │G      │
    0.9.1 ●─╯     │       │
          │F      │       │
          │       │       ●
          ●       │       │E
          │D      │       │
          │       │     ╭─●
          │       │ ╭───╯  C
          │     ╭─●─╯
          │ ╭───╯  B
    0.9.0 ●─╯
          │A
          ┊
```

Each column corresponds to a branch.

- TP/M is the original template project's ```master``` branch.
- YP/M is your project's ```master``` branch.
- TP/RTP is your project's ```rewritten-template-project``` branch'.

The ```rewritten-template-project``` branch is created automatically
by PHPProjectInitializer when you create the project.
It is simply the template project with names and config settings
rewritten to match those for your new project
(these 'rewrite rules' are stored in .ppi-settings.json).
Your project's ```master``` branch is initialized to the same initial commit
as on ```rewritten-template-project```.

After the template project has been updated, one can re-run the rewriting process,
overwrite all files on the ```rewritten-template-project``` branch, creating a new commit.
The changes between that and the previous ```rewritten-template-project``` commit
then reflect the changes in the template project, but with all the names swapped
out to match those in your project.

Merging those changes into the ```master``` branch will then have the same
result as if work on the template project was actually done in a branch on yours,
with all appropriate names and paths.

So let's tell what would happen to produce the above diagram.

- A: Someone makes a commit to the template project and tags it as 0.9.0.
- B: Someone creates a new project based on the latest (0.9.0) tag from the template project
- C: You start work on your project and commit some changes
- D: Work is done on template project without creating a new tag
- E: More work committed on your project
- F: New version of template project is released with tag 0.9.1.

At this point you decide to get the latest greatest stuff from the template,
so you run the upgrader, which automatically does the following:

- G: Rewrite the template project using your project's rewrite rules
  to create a new commit on your ```rewritten-template-project``` branch
  with the previous rewriting result (B) as the parent.
- H: Merge the changes since the previous template rewrite commit
  into the ```master``` branch.  i.e. merge changesets B-G with B-C-E
  to produce merge commit H on ```master```.

The tool that does that last step for you is a script called ```bin/update-project```
in a project called ```PHPProjectRewriter```.
There are a lot of things that can go wrong during this process,
so it will try to be as helpful as possible,
and it will not automatically create merge commits for you,
instead letting you check its work before committing.

```PHPProjectRewriter``` is actually used under the hood by ```PHPProjectInitializer```
to create the initial ```rewritten-template-project``` commit.
