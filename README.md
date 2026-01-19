# GoSummarize

This project contains two applications.

- A backend one, using Symfony and ApiPlatform. It allows to fetch everyday your favorite feeds (RSS) and store new published pages. Everything (feeds, pages) are then accessible through a REST API.
- A mobile App. It is used for triage: run it every day to select if you want to 1) Ignore the page (DISCARD), 2) Read it later (TO_READ) or 3) to decide how to summarize it (TO_SUMMARIZE). This last status can be used for instance if you want to use AI to summarize theses pages (it is an example, and no such feature is given in the repository).

- [Backend](server)
- [App](mobile)
