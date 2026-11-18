import PostController from './PostController'
import CommentController from './CommentController'
import ReviewController from './ReviewController'

const Community = {
    PostController: Object.assign(PostController, PostController),
    CommentController: Object.assign(CommentController, CommentController),
    ReviewController: Object.assign(ReviewController, ReviewController),
}

export default Community