# LIGO Council Delegate COmanage Registry Plugin

### Background
The LSC Council is composed of selected members of the LIGO Scientific
Collaboration. Each MOU Group in the LSC is allowed to appoint `N` delegates
as defined by:

```
N = CEILING(GWC/5)

where:

MWC = Sum of TWC for all members of MOU
```

The definition of `MWC` is found in the LSC ByLaws and should be implemented
in COmanage by another plugin (currently being written by Mike Manske) in such
a way that every person in the LSC has a `TWC` defined before their enrollment
is finalized.

Council delegates are assigned in an interface accessible to _Group Managers_
for the MOU Group. Group Managers are designated by the MOU PI in a separate
interface.

### Workflow
In the current system, the workflow is as follows:

1. A Group Manager signs into the identity management platform and selects the
`Manage Council Delegates` interface.
1. At the top of the `Manage Council Delegates` interface is an explanation of
the rules governing the number N and a statement of the number N for the MOU
group being managed (note that a person can be a group manager of more than
one MOU group and could therefore be assigning council delegates for more than
one MOU group - currently that would be done through separate instances of the
interface).
1. The `Manage Council Delegates` interface lists all members of the MOU group
in question with checkboxes beside their names and allows up to N checkboxes
to be checked.

### Notifications
When MOU group changes are made by a Group Manager that reduce the `GWC` of an
MOU group to the point where the number of council delegates is lowered, the
Group Manager making the changes should be required to reduce the number of
council delegates to or below the new maximum allotment before the MOU group
changes can be finalized. When MOU group changes lead to an increase in the
number of allow council delegates for the MOU group, the Group Manager making
the changes should be notified and invited to change the council delegates but
not required to in order to finalize the MOU group changes. 

Furthermore, if the number of council delegates assigned in an MOU group is
below the allotted number `N`, an email should be sent on a quarterly basis to
the MOU PI reminding them that they are allowed to assign more council
delegates.
