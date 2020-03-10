export interface IPost {
  id: string;
  created_time: string;
  full_picture: string;
  is_hidden: boolean;
  is_published: boolean;
  message: string;
  permalink_url: string;
  picture: string;
  promotable_id: string;
  status_type: string;
  is_eligible_for_promotion: boolean;
  link: string;
  selected_post: boolean;
}
