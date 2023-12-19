[Па-беларуску](https://github.com/gitReiko/moodle-public-need-to-do/blob/main/README_BY.md)

## What is this?

Block type plugin *Need to do* for Moodle 4.1

## Purpose

Providing information about work checks and communication in a convenient format for:

- an overview of the work that needs to be done
- simplifying the checks of works
- control over the timeliness of work checks

## Block can work with

- quizes
- assignments
- forums (only posts reading)
- site chat

## Other features

- determining the timeliness of the check 
- determining the time after which the work should not be checked (ignoring work that has gone unchecked for a very long time)
- setting up different instances of blocks that can work with different categories of courses and different cohorts of teachers
- possibility of disabling unnecessary types of checks, e.g. forums or assignments

## Installation

1. сreate a cohort with teachers who will work with the block
2. download the archive from github (Сode button)
3. go to the Moodle plugins installation page
4. upload plugin archive
5. follow the instructions of the installer
6. choose a cohort with teachers
7. now you can place the block in the desired location

## About the block's operation

**Attention!!! The block considers that the teacher checks the work when the following conditions are met:**

1. the teacher must be enrolled in the cohort the block is working with
2. teacher and student must be enrolled in the same **course group**
3. the teacher must have the permission to check the work (teacher's role is giving all the necessary permissions)
4. the teacher should not be blocked (suspended)

To update the data, you need to click the *Update data* button or wait for the task to complete (**\block_needtodo\task\cache_data**). The task is usually performed once per hour. You can change the update frequency in the task schedule.

## Author

Denis Makouski (Reiko)

## License

Apache-2.0 License

