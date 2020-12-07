# LIGO Council Delegate COmanage Registry Plugin

### Definitions
The rules for council delegates are defined in the [LSC Bylaws](https://dcc.ligo.org/LIGO-M050172). Some useful definitions from that document and elsewhere:
- An _MOU_ Group is one or more institutions (universities, research labs,
  etc) which have a Memorandum of Understanding (MOU) with the LSC.
- A _Consortium MOU Group_ is an LSC MOU group consisting of more than one institution.
- A _Group Manager_ is a person designated by the MOU PI to approve MOU Group
  enrollments, set start and termination dates for MOU Group members, perform
  other administrative taks related to MOU group personnel.
- A _Member Work Contribution_ (MWC) is a number attached to a person and an
  MOU Group to which that person belongs as defined in the LSC Bylaws. Every
  member of every MOU group will have an MWC defined for that group membership
  in COmanage.
- A _Group Work Contribution_ (GWC) is a number attached to an MOU group that
  is the sum of all the MWCs for members of the MOU group.

### Background
The LSC Council is composed of selected members of the LIGO Scientific
Collaboration. Each MOU Group in the LSC is allowed to appoint `N` delegates
as defined by:

```
N = CEILING(GWC/5)
```

Council delegates for an MOU Group are assigned in an interface accessible to
only to Group Managers for the MOU Group. 

### Workflow
In the current system, the workflow is as follows:

1. A Group Manager signs into the identity management platform and selects the
`Manage Council Delegates` interface.
1. At the top of the `Manage Council Delegates` interface is an explanation of
the rules governing the number `N` and a statement of the number N for the MOU
Group being managed (note that a person can be a group manager of more than
one MOU Group and could therefore be assigning council delegates for more than
one MOU Group - currently that would be done through separate instances of the
interface).
1. If the MOU Group in question is a Consortium MOU Group, a table showing
each institution in the Consortium MOU Group, the sum of of MWC from that
instutution, and the portion of `N` being contributed (calculated as
`round(N*sum(Insitutional MWC)/GWC)` for that institution.
1. The `Manage Council Delegates` interface lists all members of the MOU group
in question with checkboxes beside their names and allows up to `N` checkboxes
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
below the allotted number `N`, an email should be sent on a semi-anual basis
to the MOU PI reminding them that they are allowed to assign more council
delegates.
