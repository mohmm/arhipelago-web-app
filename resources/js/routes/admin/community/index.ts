import posts from './posts'
import comments from './comments'
import reviews from './reviews'

const community = {
    posts: Object.assign(posts, posts),
    comments: Object.assign(comments, comments),
    reviews: Object.assign(reviews, reviews),
}

export default community