import pointsOfInterest from './points-of-interest'
import experiences from './experiences'
import destinations from './destinations'
import lookups from './lookups'
import community from './community'
import users from './users'
import roles from './roles'
import maya from './maya'
import legalDocuments from './legal-documents'

const admin = {
    pointsOfInterest: Object.assign(pointsOfInterest, pointsOfInterest),
    experiences: Object.assign(experiences, experiences),
    destinations: Object.assign(destinations, destinations),
    lookups: Object.assign(lookups, lookups),
    community: Object.assign(community, community),
    users: Object.assign(users, users),
    roles: Object.assign(roles, roles),
    maya: Object.assign(maya, maya),
    legalDocuments: Object.assign(legalDocuments, legalDocuments),
}

export default admin