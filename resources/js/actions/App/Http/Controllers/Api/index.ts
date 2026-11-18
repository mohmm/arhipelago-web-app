import AuthController from './AuthController'
import AppConfigController from './AppConfigController'
import CategoryController from './CategoryController'
import MapController from './MapController'
import PointOfInterestController from './PointOfInterestController'
import ReviewController from './ReviewController'
import ExperienceController from './ExperienceController'
import PostController from './PostController'
import CommentController from './CommentController'
import UserController from './UserController'
import MeController from './MeController'
import LikeController from './LikeController'
import FollowController from './FollowController'
import MayaConversationController from './MayaConversationController'
import MayaMessageController from './MayaMessageController'

const Api = {
    AuthController: Object.assign(AuthController, AuthController),
    AppConfigController: Object.assign(AppConfigController, AppConfigController),
    CategoryController: Object.assign(CategoryController, CategoryController),
    MapController: Object.assign(MapController, MapController),
    PointOfInterestController: Object.assign(PointOfInterestController, PointOfInterestController),
    ReviewController: Object.assign(ReviewController, ReviewController),
    ExperienceController: Object.assign(ExperienceController, ExperienceController),
    PostController: Object.assign(PostController, PostController),
    CommentController: Object.assign(CommentController, CommentController),
    UserController: Object.assign(UserController, UserController),
    MeController: Object.assign(MeController, MeController),
    LikeController: Object.assign(LikeController, LikeController),
    FollowController: Object.assign(FollowController, FollowController),
    MayaConversationController: Object.assign(MayaConversationController, MayaConversationController),
    MayaMessageController: Object.assign(MayaMessageController, MayaMessageController),
}

export default Api