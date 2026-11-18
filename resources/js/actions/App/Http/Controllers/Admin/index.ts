import PointOfInterestController from './PointOfInterestController'
import PointOfInterestPhotoController from './PointOfInterestPhotoController'
import ExperienceController from './ExperienceController'
import DestinationController from './DestinationController'
import LookupController from './LookupController'
import Lookups from './Lookups'
import Community from './Community'
import UserController from './UserController'
import RoleController from './RoleController'
import MayaSettingsController from './MayaSettingsController'
import LegalDocumentController from './LegalDocumentController'

const Admin = {
    PointOfInterestController: Object.assign(PointOfInterestController, PointOfInterestController),
    PointOfInterestPhotoController: Object.assign(PointOfInterestPhotoController, PointOfInterestPhotoController),
    ExperienceController: Object.assign(ExperienceController, ExperienceController),
    DestinationController: Object.assign(DestinationController, DestinationController),
    LookupController: Object.assign(LookupController, LookupController),
    Lookups: Object.assign(Lookups, Lookups),
    Community: Object.assign(Community, Community),
    UserController: Object.assign(UserController, UserController),
    RoleController: Object.assign(RoleController, RoleController),
    MayaSettingsController: Object.assign(MayaSettingsController, MayaSettingsController),
    LegalDocumentController: Object.assign(LegalDocumentController, LegalDocumentController),
}

export default Admin