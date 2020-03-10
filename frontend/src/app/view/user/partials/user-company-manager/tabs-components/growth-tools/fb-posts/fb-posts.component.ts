import { Component, Input, OnInit } from '@angular/core';
import { IPost } from '../../../../../../../interfaces/user.interface';

@Component({
  selector: 'fb-posts',
  templateUrl: './fb-posts.component.html',
  styleUrls: ['./fb-posts.component.scss']
})
export class FbPostsComponent implements OnInit {
  public _items: IPost[] = [];
  public _widgetById: any;
  public _widgetConfig: any;
  public _preloader: boolean;
  @Input() closePopup;
  @Input('items') set items(items) {
    if (items) {
      this._items = items;
    }
  }
  get items() {
    return this._items;
  }

  @Input('widgetById') set widgetById(widgetById) {
    if (widgetById) {
      this._widgetById = widgetById;
    }
  }
  get widgetById() {
    return this._widgetById;
  }

  @Input('widgetConfig') set widgetConfig(widgetConfig) {
    if (widgetConfig) {
      this._widgetConfig = widgetConfig;
    }
  }
  get widgetConfig() {
    return this._widgetConfig;
  }

  @Input('preloader') set preloader(preloader) {
    this._preloader = preloader;
  }
  get preloader() {
    return this._preloader;
  }

  constructor(
  ) { }

  ngOnInit() {
  }

  /**
   * Update post
   * @param post {object}
   * @param status {boolean}
   */
  public updateChangePost(post, status) {
    if (status) {
      post.selected_post = true;
      this._widgetById.details.postId = post.id;
      this._widgetConfig.selected_post_item = post;
      this.closePopup();
    }
  }

}
